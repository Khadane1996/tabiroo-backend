<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;



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
                'client_id' => 'required|exists:users,id',
                'chef_id' => 'required|exists:users,id',
                'sous_total' => 'required|string',
                'frais_service' => 'required|string',
                'nombre_convive' => 'required|string',
                'date_prestation' => 'required|string',
                'choix' => 'required|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $status = 'pending';
            if($request->choix === 'oui') {
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
                'status' => $status
            ]);

            Notification::notifyReservation($request->chef_id, $reservation->id);
                
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
            $status = 'pending';
            if ($request->choix === 'oui') {
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
            ]);

            // Créer PaymentIntent avec destination charges (paiement direct au chef)
            $pi = $this->stripe->createPaymentIntentWithDestination(
                $request->amount,
                'eur',
                $request->chef_stripe_account_id,
                $request->frais_service // Les frais restent sur le compte principal
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

            // Notification::notifyReservation($request->chef_id, $reservation->id);

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

            // Vérifier si le client est bien le propriétaire
            // if ($reservation->client_id !== $request->user()->id) {
            // if ($reservation->client_id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Accès non autorisé'
            //     ], 403);
            // }

            $reservation->status = $request->status;
            if($request->motif){
                $reservation->motif = $request->motif;
            }
            $reservation->save();

            Notification::notifyReservationAnnuler($reservation->chef_id, $reservation->client_id, $reservation->id);

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
