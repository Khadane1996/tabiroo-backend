<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reservation;
use App\Models\Notification;
use App\Models\MenuPrestation;
use Illuminate\Support\Facades\DB;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;
use App\Models\User;



class ReservationController extends Controller
{
    protected $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
    }

    public function index($user_id)
    {
        $reservations = Reservation::with('menuPrestation.menu','chef')
        ->where('client_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'menu_prestation_id' => 'required|exists:menu_prestation,id',
                'client_id'          => 'required|exists:users,id',
                'chef_id'            => 'required|exists:users,id',
                'sous_total'         => 'required|string',
                'frais_service'      => 'required|string',
                'nombre_convive'     => 'required|string',
                'date_prestation'    => 'required|string',
                'choix'              => 'required|string',
                'is_private'         => 'sometimes|boolean',
                'private_message'    => 'nullable|string|max:255',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            // Récupérer la prestation liée pour appliquer les règles de privatisation
            $menuPrestation = MenuPrestation::findOrFail($request->menu_prestation_id);
            $prestationId   = $menuPrestation->prestation_id;

            $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

            // Si une réservation privée a déjà été acceptée pour cette prestation, interdire toute nouvelle réservation
            $hasPrivateAccepted = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                    $q->where('prestation_id', $prestationId);
                })
                ->where('is_private', true)
                ->whereIn('status', ['accepted'])
                ->exists();

            if ($hasPrivateAccepted) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cette prestation est déjà privatisée et ne peut plus être réservée.',
                ], 409);
            }

            // Si le client demande une privatisation mais qu’il existe déjà des réservations, refuser
            if ($isPrivate) {
                $existingReservations = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                        $q->where('prestation_id', $prestationId);
                    })
                    ->whereIn('status', ['pending', 'accepted', 'upcoming', 'completed'])
                    ->count();

                if ($existingReservations > 0) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'La privatisation est impossible : des réservations ont déjà été effectuées par d’autres convives.',
                    ], 409);
                }
            }

            // Statut : si privatisation, toujours en attente de validation du chef (même si la prestation est en validation automatique)
            $status = 'pending';
            if (!$isPrivate && $request->choix === 'oui') {
                $status = 'accepted';
            }

            $reservation = Reservation::create([
                'menu_prestation_id' => $request->menu_prestation_id,
                'client_id' => $request->client_id,
                'chef_id' => $request->chef_id,
                'sous_total' => $request->sous_total,
                'frais_service' => $request->frais_service,
                'nombre_convive' => $request->nombre_convive,
                'date_prestation' => $request->date_prestation,
                'transaction_detail' => $request->transaction_detail,
                'status' => $status,
                'is_private' => $isPrivate,
                'private_message' => $request->private_message,
            ]);

            Notification::notifyReservation($request->chef_id, $reservation->id);

            // Notifier le client avec l'adresse du chef
            $chef = User::with('adresse')->find($request->chef_id);
            Notification::notifyChefAddressToClient($chef, $reservation);
                
            return response()->json([
                'status' => true,
                'message' => 'Réservation créée avec succès',
                'reservation' => $reservation
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Réserver et payer de façon atomique
     * Etapes:
     *  - Créer la réservation en pending
     *  - Créer le PaymentIntent (connect -> chef)
     *  - Confirmer côté client avec client_secret
     *  - Au webhook: marquer payée. Ici on permet aussi de finaliser si déjà succeeded
     */
    public function reserveAndPay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_prestation_id' => 'required|exists:menu_prestation,id',
            'client_id' => 'required|exists:users,id',
            'chef_id' => 'required|exists:users,id',
            'sous_total' => 'required|numeric',
            'frais_service' => 'required|numeric',
            'nombre_convive' => 'required|numeric',
            'date_prestation' => 'required|string',
            'choix' => 'required|string',
            'chef_stripe_account_id' => 'required|string',
            'amount' => 'required|numeric|min:1', // en euros
            'is_private' => 'sometimes|boolean',
            'private_message' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Récupérer la prestation liée pour appliquer les règles de privatisation
            $menuPrestation = MenuPrestation::findOrFail($request->menu_prestation_id);
            $prestationId   = $menuPrestation->prestation_id;

            $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

            // Si une réservation privée a déjà été acceptée pour cette prestation, interdire toute nouvelle réservation
            $hasPrivateAccepted = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                    $q->where('prestation_id', $prestationId);
                })
                ->where('is_private', true)
                ->whereIn('status', ['accepted'])
                ->exists();

            if ($hasPrivateAccepted) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cette prestation est déjà privatisée et ne peut plus être réservée.',
                ], 409);
            }

            // Si le client demande une privatisation mais qu’il existe déjà des réservations, refuser
            if ($isPrivate) {
                $existingReservations = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                        $q->where('prestation_id', $prestationId);
                    })
                    ->whereIn('status', ['pending', 'accepted', 'upcoming', 'completed'])
                    ->count();

                if ($existingReservations > 0) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'La privatisation est impossible : des réservations ont déjà été effectuées par d’autres convives.',
                    ], 409);
                }
            }

            // Statut : si privatisation, toujours en attente de validation du chef (même si la prestation est en validation automatique)
            $status = 'pending';
            if (!$isPrivate && $request->choix === 'oui') {
                $status = 'accepted';
            }

            $reservation = Reservation::create([
                'menu_prestation_id' => $request->menu_prestation_id,
                'client_id' => $request->client_id,
                'chef_id' => $request->chef_id,
                'sous_total' => (string)$request->sous_total,
                'frais_service' => (string)$request->frais_service,
                'nombre_convive' => (string)$request->nombre_convive,
                'date_prestation' => $request->date_prestation,
                'transaction_detail' => null,
                'status' => $status,
                'is_private' => $isPrivate,
                'private_message' => $request->private_message,
            ]);

            // Générer un code de validation (à communiquer au chef après le repas)
            $reservation->validation_code = (string) random_int(100000, 999999);
            $reservation->save();

            // Récupérer le client pour attacher un Stripe Customer au PaymentIntent (si disponible)
            $customerId = null;
            try {
                $client = User::find($request->client_id);
                if ($client && $client->stripe_customer_id) {
                    $customerId = $client->stripe_customer_id;
                }
            } catch (\Throwable $e) {
                Log::warning('Impossible de récupérer le stripe_customer_id du client', [
                    'client_id' => $request->client_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Créer un PaymentIntent sur le compte plateforme (mode escrow)
            $pi = $this->stripe->createEscrowPaymentIntent(
                $request->amount,
                'eur',
                $customerId,
                [
                    'reservation_id' => $reservation->id,
                    'client_id' => $request->client_id,
                    'chef_id' => $request->chef_id,
                    'chef_stripe_account_id' => $request->chef_stripe_account_id,
                ]
            );

            // Stocker l'id du PI sur la réservation
            $reservation->payment_intent_id = $pi->id;
            $reservation->chef_amount = $request->sous_total; // Montant pour le chef
            $reservation->commission_amount = $request->frais_service; // Commission de l'app
            $reservation->transaction_detail = json_encode([
                'payment_intent_id' => $pi->id,
                'metadata' => [
                    'chef_stripe_account_id' => $request->chef_stripe_account_id
                ]
            ]);
            $reservation->save();

            // Notification au chef pour la nouvelle réservation
            Notification::notifyReservation($request->chef_id, $reservation->id);

            // Notification au client avec l'adresse du chef pour la prestation
            $chefUser = User::with('adresse')->find($request->chef_id);
            Notification::notifyChefAddressToClient($chefUser, $reservation);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Réservation initialisée, confirmer le paiement',
                'reservation' => $reservation,
                'client_secret' => $pi->client_secret,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annuler la réservation si le paiement a échoué côté client
     */
    public function cancelOnPaymentFail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:reservations,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reservation = Reservation::findOrFail($request->reservation_id);
            $reservation->delete();
            return response()->json(['status' => true, 'message' => 'Réservation annulée suite à échec de paiement']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            $oldStatus = $reservation->status;

            $reservation->status = $request->status;
            if ($request->motif) {
                $reservation->motif = $request->motif;
            }
            $reservation->save();

            // Notifications en fonction du nouveau statut
            if ($oldStatus !== $reservation->status) {
                // Réservation acceptée
                if ($reservation->status === 'accepted') {
                    Notification::notifyReservationAcceptedForClient($reservation);
                    Notification::notifyReservationAcceptedForChef($reservation);
                }
                // Réservation annulée
                if ($reservation->status === 'cancelled') {
                    Notification::notifyReservationAnnuler($reservation->chef_id, $reservation->client_id, $reservation->id);
                    Notification::notifyReservationCancelledForClient($reservation);
                }
            }

            // Si la réservation est annulée (client ou chef) => rembourser intégralement si possible
            if ($oldStatus !== 'cancelled' && $reservation->status === 'cancelled') {
                try {
                    if ($reservation->payment_intent_id && !$reservation->refunded_at) {
                        $refund = $this->stripe->refundPaymentIntent($reservation->payment_intent_id);
                        $reservation->refund_id = $refund->id ?? null;
                        $reservation->refunded_at = now();
                        $reservation->save();
                    }
                } catch (\Throwable $e) {
                    Log::error('Erreur lors du remboursement de la réservation annulée', [
                        'reservation_id' => $reservation->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Réservation annulée avec succès',
                'reservation' => $reservation
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une réservation côté chef en saisissant le code communiqué par le client.
     * Cette action marque la réservation comme terminée et déclenche la distribution du paiement.
     */
    public function validateWithCode(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $user = $request->user();
            $reservation = Reservation::with('chef')->findOrFail($id);

            if (!$user || $user->id !== $reservation->chef_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès non autorisé pour cette réservation',
                ], 403);
            }

            if (!in_array($reservation->status, ['accepted', 'upcoming'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cette réservation ne peut pas être validée dans son état actuel.',
                ], 422);
            }

            if (!$reservation->validation_code) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun code de validation n’est associé à cette réservation.',
                ], 400);
            }

            if ($reservation->validation_code !== $request->code) {
                return response()->json([
                    'status' => false,
                    'message' => 'Code de validation incorrect.',
                ], 400);
            }

            if ($reservation->payment_distributed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Le paiement a déjà été distribué pour cette réservation.',
                ], 400);
            }

            if (!$reservation->payment_intent_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun paiement n’est associé à cette réservation.',
                ], 400);
            }

            $chef = $reservation->chef;
            if (!$chef || !$chef->stripe_account_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Le chef n’a pas de compte Stripe valide pour recevoir le paiement.',
                ], 400);
            }

            try {
                $transfer = $this->stripe->transferToChef(
                    $reservation->payment_intent_id,
                    $chef->stripe_account_id,
                    (float) $reservation->chef_amount,
                    'reservation_'.$reservation->id
                );

                $reservation->payment_distributed = true;
                $reservation->transfer_id = $transfer->id ?? null;
                $reservation->distributed_at = now();
                $reservation->status = 'completed';
                $reservation->validation_code_used_at = now();
                $reservation->save();

                // Notifier le chef que le paiement a été distribué
                Notification::notifyPaymentDistributed($reservation->chef_id, $reservation->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Réservation validée et paiement distribué avec succès.',
                    'reservation' => $reservation,
                ], 200);
            } catch (\Throwable $e) {
                Log::error('Erreur lors de la distribution du paiement Stripe', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Impossible de distribuer le paiement au chef.',
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getReservationForChef($user_id)
    {
        $reservations = Reservation::with('menuPrestation.prestation.typeDeRepas','client')
        ->where('chef_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }

    public function getNotication($user_id)
    {
        $notifications = Notification::with('user')
        ->where('user_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes notifications',
            'data' => $notifications
        ]);
    }


    /**
     * Récupérer les détails de paiement d'une réservation
     */
    public function getPaymentDetails($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);
            
            if (!$reservation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Réservation introuvable'
                ], 404);
            }
            
            return response()->json([
                'status' => true,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'payment_intent_id' => $reservation->payment_intent_id,
                    'status' => $reservation->status,
                    'payment_distributed' => $reservation->payment_distributed,
                    'chef_amount' => $reservation->chef_amount,
                    'commission_amount' => $reservation->commission_amount,
                    'transfer_id' => $reservation->transfer_id,
                    'distributed_at' => $reservation->distributed_at,
                    'created_at' => $reservation->created_at,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la récupération des détails',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
