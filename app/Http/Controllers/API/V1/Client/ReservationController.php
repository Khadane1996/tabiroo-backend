<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use App\Enums\ReservationStatus;
use App\Services\StripeService;
use App\Services\PaymentCalculationService;
use App\Services\ReservationStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Models\Notification;
use App\Models\MenuPrestation;
use App\Models\User;

class ReservationController extends Controller
{
    protected StripeService $stripe;
    protected PaymentCalculationService $calculator;
    protected ReservationStateMachine $stateMachine;

    public function __construct(
        StripeService $stripe,
        PaymentCalculationService $calculator,
        ReservationStateMachine $stateMachine
    ) {
        $this->stripe = $stripe;
        $this->calculator = $calculator;
        $this->stateMachine = $stateMachine;
    }

    public function index($user_id)
    {
        $reservations = Reservation::with('menuPrestation.menu', 'chef')
            ->where('client_id', $user_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }

    /**
     * Endpoint de calcul de prix (preview avant paiement)
     * GET /api/client/reservation/calculate-price?menu_price=XX&nombre_convive=YY
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_price' => 'required|numeric|min:0.01',
            'nombre_convive' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $breakdown = $this->calculator->calculateBreakdown(
            (float) $request->menu_price,
            (int) $request->nombre_convive
        );

        return response()->json([
            'status' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Creer une reservation simple (sans paiement)
     */
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
                ], 422);
            }

            $privatisationCheck = $this->checkPrivatisation($request);
            if ($privatisationCheck) {
                return $privatisationCheck;
            }

            $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

            // Determiner le flux
            $isAutomatic = !$isPrivate && $request->choix === 'oui';
            $status = $isAutomatic
                ? ReservationStatus::CONFIRMED
                : ReservationStatus::DRAFT;

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
                'flow_type' => $isAutomatic ? 'automatic' : 'manual',
            ]);

            if ($status === ReservationStatus::CONFIRMED) {
                Notification::notifyAutoConfirmed($request->chef_id, $reservation->id);
                Notification::notifyReservationAcceptedForClient($reservation);
            } else {
                Notification::notifyReservation($request->chef_id, $reservation->id);
            }

            $chef = User::with('adresse')->find($request->chef_id);
            Notification::notifyChefAddressToClient($chef, $reservation);

            return response()->json([
                'status' => true,
                'message' => 'Reservation creee avec succes',
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
     * Reserver et payer (flux CDC complet)
     * Flux 1: choix=oui (auto) -> capture_method=automatic
     * Flux 2: choix=non ou privatisation -> capture_method=manual
     */
    public function reserveAndPay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_prestation_id' => 'required|exists:menu_prestation,id',
            'client_id' => 'required|exists:users,id',
            'chef_id' => 'required|exists:users,id',
            'sous_total' => 'required|numeric',
            'nombre_convive' => 'required|numeric|min:1',
            'date_prestation' => 'required|string',
            'choix' => 'required|string',
            'chef_stripe_account_id' => 'required|string',
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
            $privatisationCheck = $this->checkPrivatisation($request);
            if ($privatisationCheck) {
                return $privatisationCheck;
            }

            $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

            // Determiner le flux selon le CDC
            // Flux 1 (automatique): choix=oui ET pas de privatisation
            // Flux 2 (manuel): choix=non OU privatisation
            $isAutomatic = !$isPrivate && $request->choix === 'oui';
            $captureMethod = $isAutomatic ? 'automatic' : 'manual';
            $flowType = $isAutomatic ? 'automatic' : 'manual';

            // Calcul des montants selon le CDC
            $menusAmount = (float) $request->sous_total;
            $breakdown = $this->calculator->calculateFromTotal($menusAmount);

            // Statut initial selon le CDC
            $initialStatus = $isAutomatic
                ? ReservationStatus::DRAFT
                : ReservationStatus::DRAFT;

            $reservation = Reservation::create([
                'menu_prestation_id' => $request->menu_prestation_id,
                'client_id' => $request->client_id,
                'chef_id' => $request->chef_id,
                'sous_total' => $breakdown['menus_amount'],
                'frais_service' => $breakdown['stripe_fee'],
                'nombre_convive' => (int) $request->nombre_convive,
                'date_prestation' => $request->date_prestation,
                'status' => $initialStatus,
                'is_private' => $isPrivate,
                'private_message' => $request->private_message,
                'capture_method' => $captureMethod,
                'flow_type' => $flowType,
                'chef_amount' => $breakdown['host_payout'],
                'commission_amount' => $breakdown['commission'],
                'stripe_fee_amount' => $breakdown['stripe_fee'],
                'total_charged' => $breakdown['total_guest'],
                'validation_code' => (string) random_int(100000, 999999),
            ]);

            // Definir les timers pour Flux 2
            if (!$isAutomatic) {
                $reservation->host_response_deadline = now()->addHours(4);
                $reservation->save();
            }

            // Recuperer le Stripe Customer ID du client
            $customerId = null;
            try {
                $client = User::find($request->client_id);
                if ($client && $client->stripe_customer_id) {
                    $customerId = $client->stripe_customer_id;
                }
            } catch (\Throwable $e) {
                Log::warning('Impossible de recuperer le stripe_customer_id', [
                    'client_id' => $request->client_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Creer le PaymentIntent avec le montant total (menus + frais)
            $pi = $this->stripe->createEscrowPaymentIntent(
                $breakdown['total_guest'],
                'eur',
                $customerId,
                [
                    'reservation_id' => $reservation->id,
                    'client_id' => $request->client_id,
                    'chef_id' => $request->chef_id,
                    'host_id' => $request->chef_id,
                    'chef_stripe_account_id' => $request->chef_stripe_account_id,
                    'menus_amount' => (string) $breakdown['menus_amount'],
                    'type' => $isAutomatic ? 'auto' : 'manual',
                ],
                $captureMethod
            );

            // Stocker les infos Stripe
            $reservation->payment_intent_id = $pi->id;
            $reservation->transaction_detail = json_encode([
                'payment_intent_id' => $pi->id,
                'breakdown' => $breakdown,
                'metadata' => [
                    'chef_stripe_account_id' => $request->chef_stripe_account_id,
                ],
            ]);

            // Pour Flux 2, passer en PENDING_HOST_RESPONSE
            if (!$isAutomatic) {
                $reservation->status = ReservationStatus::PENDING_HOST_RESPONSE;
            }
            $reservation->save();

            // Notifications
            if ($isAutomatic) {
                Notification::notifyAutoConfirmed($request->chef_id, $reservation->id);
                Notification::notifyReservationAcceptedForClient($reservation);
            } else {
                Notification::notifyReservation($request->chef_id, $reservation->id);
            }

            $chefUser = User::with('adresse')->find($request->chef_id);
            Notification::notifyChefAddressToClient($chefUser, $reservation);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Reservation initialisee, confirmer le paiement',
                'reservation' => $reservation->fresh(),
                'client_secret' => $pi->client_secret,
                'breakdown' => $breakdown,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur reserveAndPay', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annuler la reservation si le paiement a echoue cote client
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
            // Annuler le PaymentIntent si en mode manual
            if ($reservation->payment_intent_id && $reservation->capture_method === 'manual') {
                try {
                    $this->stripe->cancelPaymentIntent($reservation->payment_intent_id);
                } catch (\Throwable $e) {
                    Log::warning('Echec annulation PI sur payment fail', ['error' => $e->getMessage()]);
                }
            }
            $reservation->delete();
            return response()->json(['status' => true, 'message' => 'Reservation annulee suite a echec de paiement']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mise a jour d'une reservation (accepter/refuser par le chef, etc.)
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $user = $request->user();
            $requestedStatus = $request->status;

            // Gestion du motif
            if ($request->motif) {
                $reservation->motif = $request->motif;
                $reservation->save();
            }

            // === Flux 2: Hote accepte la reservation ===
            if ($requestedStatus === 'accepted' && $reservation->status === ReservationStatus::PENDING_HOST_RESPONSE) {
                return $this->handleHostAccept($reservation);
            }

            // === Flux 2: Hote refuse la reservation ===
            if ($requestedStatus === 'declined' && $reservation->status === ReservationStatus::PENDING_HOST_RESPONSE) {
                return $this->handleHostDecline($reservation);
            }

            // === Annulation par le convive ===
            if ($requestedStatus === 'cancelled' && $user && $user->id === $reservation->client_id) {
                return $this->handleGuestCancellation($reservation, $user);
            }

            // === Annulation par l'hote ===
            if ($requestedStatus === 'cancelled' && $user && $user->id === $reservation->chef_id) {
                return $this->handleHostCancellation($reservation, $user);
            }

            // Fallback: mise a jour simple du statut si valide
            if ($requestedStatus) {
                try {
                    $newStatus = ReservationStatus::from($requestedStatus);
                    $this->stateMachine->transition($reservation, $newStatus);
                } catch (\Throwable $e) {
                    // Si le statut n'est pas un enum valide, on le met tel quel pour compatibilite
                    $reservation->status = $requestedStatus;
                    $reservation->save();
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Reservation mise a jour',
                'reservation' => $reservation->fresh()
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Annulation par le convive (endpoint dedie)
     * POST /api/client/reservation/{id}/cancel
     */
    public function cancelByGuest(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $user = $request->user();

            if (!$user || $user->id !== $reservation->client_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Acces non autorise',
                ], 403);
            }

            return $this->handleGuestCancellation($reservation, $user);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Annulation par l'hote (endpoint dedie)
     * POST /api/chef/reservation/{id}/cancel
     */
    public function cancelByHost(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $user = $request->user();

            if (!$user || $user->id !== $reservation->chef_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Acces non autorise',
                ], 403);
            }

            return $this->handleHostCancellation($reservation, $user);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Valider une reservation cote chef via code OTP
     */
    public function validateWithCode(Request $request, $id)
    {
        $request->validate(['code' => 'required|string']);

        try {
            $user = $request->user();
            $reservation = Reservation::with('chef')->findOrFail($id);

            if (!$user || $user->id !== $reservation->chef_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Acces non autorise pour cette reservation',
                ], 403);
            }

            // Verifier que la reservation est dans un statut valide pour OTP
            $validStatuses = [
                ReservationStatus::CONFIRMED,
                ReservationStatus::COMPLETED_PENDING_OTP,
            ];
            if (!in_array($reservation->status, $validStatuses)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cette reservation ne peut pas etre validee dans son etat actuel.',
                ], 422);
            }

            if (!$reservation->validation_code) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun code de validation associe a cette reservation.',
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
                    'message' => 'Le paiement a deja ete distribue.',
                ], 400);
            }

            if (!$reservation->payment_intent_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun paiement associe a cette reservation.',
                ], 400);
            }

            $chef = $reservation->chef;
            if (!$chef || !$chef->stripe_account_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Le chef n\'a pas de compte Stripe valide.',
                ], 400);
            }

            // Transition: COMPLETED_VALIDATED
            $reservation->status = ReservationStatus::COMPLETED_VALIDATED;
            $reservation->validation_code_used_at = now();
            $reservation->save();

            // Declencher le payout
            try {
                $transfer = $this->stripe->transferToChef(
                    $reservation->payment_intent_id,
                    $chef->stripe_account_id,
                    (float) $reservation->chef_amount,
                    'reservation_' . $reservation->id
                );

                $reservation->payment_distributed = true;
                $reservation->transfer_id = $transfer->id ?? null;
                $reservation->distributed_at = now();
                $reservation->payout_initiated_at = now();
                $reservation->status = ReservationStatus::PAYOUT_INITIATED;
                $reservation->save();

                // Marquer comme complete
                $reservation->payout_completed_at = now();
                $reservation->status = ReservationStatus::PAYOUT_COMPLETED;
                $reservation->save();

                Notification::notifyPaymentDistributed($reservation->chef_id, $reservation->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Reservation validee et paiement distribue avec succes.',
                    'reservation' => $reservation->fresh(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Erreur distribution paiement Stripe', [
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
        $reservations = Reservation::with('menuPrestation.prestation.typeDeRepas', 'client')
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

    public function getPaymentDetails($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reservation introuvable'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'payment_intent_id' => $reservation->payment_intent_id,
                    'status' => $reservation->status,
                    'status_label' => $reservation->status_label,
                    'payment_distributed' => $reservation->payment_distributed,
                    'sous_total' => $reservation->sous_total,
                    'frais_service' => $reservation->frais_service,
                    'stripe_fee_amount' => $reservation->stripe_fee_amount,
                    'total_charged' => $reservation->total_charged,
                    'chef_amount' => $reservation->chef_amount,
                    'commission_amount' => $reservation->commission_amount,
                    'transfer_id' => $reservation->transfer_id,
                    'distributed_at' => $reservation->distributed_at,
                    'refund_id' => $reservation->refund_id,
                    'refund_status' => $reservation->refund_status,
                    'refunded_at' => $reservation->refunded_at,
                    'flow_type' => $reservation->flow_type,
                    'capture_method' => $reservation->capture_method,
                    'created_at' => $reservation->created_at,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ===================== METHODES PRIVEES =====================

    /**
     * Hote accepte une reservation manuelle (Flux 2)
     */
    private function handleHostAccept(Reservation $reservation)
    {
        try {
            // Capturer le PaymentIntent
            if ($reservation->payment_intent_id && $reservation->capture_method === 'manual') {
                $this->stripe->capturePaymentIntent($reservation->payment_intent_id);
            }

            // Le webhook payment_intent.succeeded mettra a jour le statut
            // Mais on peut aussi le faire ici pour une reponse immediate
            $reservation->status = ReservationStatus::PAYMENT_CAPTURED_HELD;
            $reservation->save();

            $reservation->status = ReservationStatus::CONFIRMED;
            $reservation->save();

            Notification::notifyReservationAcceptedForClient($reservation);
            Notification::notifyReservationAcceptedForChef($reservation);

            return response()->json([
                'status' => true,
                'message' => 'Reservation acceptee et paiement capture',
                'reservation' => $reservation->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur acceptation reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Impossible de capturer le paiement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hote refuse une reservation manuelle (Flux 2)
     */
    private function handleHostDecline(Reservation $reservation)
    {
        try {
            // Annuler le PaymentIntent (pas de debit)
            if ($reservation->payment_intent_id) {
                $this->stripe->cancelPaymentIntent($reservation->payment_intent_id);
            }

            $reservation->status = ReservationStatus::DECLINED_BY_HOST;
            $reservation->cancelled_at = now();
            $reservation->cancelled_by = 'chef';
            $reservation->save();

            Notification::notifyReservationCancelledForClient($reservation);

            return response()->json([
                'status' => true,
                'message' => 'Reservation refusee',
                'reservation' => $reservation->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur refus reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annulation par le convive avec regles 48h
     */
    private function handleGuestCancellation(Reservation $reservation, User $user)
    {
        if (!$reservation->canBeCancelledByGuest()) {
            return response()->json([
                'status' => false,
                'message' => 'Cette reservation ne peut pas etre annulee dans son etat actuel.',
            ], 422);
        }

        $before48h = $reservation->isBeforePrestationDeadline(48);
        $reservation->cancelled_at = now();
        $reservation->cancelled_by = 'client';

        if ($before48h) {
            // Annulation > 48h: remboursement integral
            $reservation->status = ReservationStatus::CANCELLED_BY_GUEST_BEFORE_48H;
            $reservation->save();

            // Declencher le remboursement
            $this->processRefund($reservation);
        } else {
            // Annulation < 48h: pas de remboursement
            $reservation->status = ReservationStatus::CANCELLED_BY_GUEST_AFTER_48H;
            $reservation->save();
        }

        Notification::notifyReservationAnnuler($reservation->chef_id, $user->id, $reservation->id);
        Notification::notifyReservationCancelledForClient($reservation);

        return response()->json([
            'status' => true,
            'message' => $before48h
                ? 'Reservation annulee. Remboursement integral initie.'
                : 'Reservation annulee. Aucun remboursement (annulation < 48h).',
            'reservation' => $reservation->fresh(),
        ]);
    }

    /**
     * Annulation par l'hote (remboursement toujours integral)
     */
    private function handleHostCancellation(Reservation $reservation, User $user)
    {
        if (!$reservation->canBeCancelledByHost()) {
            return response()->json([
                'status' => false,
                'message' => 'Cette reservation ne peut pas etre annulee dans son etat actuel.',
            ], 422);
        }

        // Si en PENDING_HOST_RESPONSE (Flux 2), annuler le PI sans debit
        if ($reservation->status === ReservationStatus::PENDING_HOST_RESPONSE) {
            return $this->handleHostDecline($reservation);
        }

        $reservation->cancelled_at = now();
        $reservation->cancelled_by = 'chef';
        $reservation->status = ReservationStatus::CANCELLED_BY_HOST;
        $reservation->save();

        // Remboursement integral
        $this->processRefund($reservation);

        Notification::notifyReservationAnnuler($reservation->chef_id, $reservation->client_id, $reservation->id);
        Notification::notifyReservationCancelledForClient($reservation);

        return response()->json([
            'status' => true,
            'message' => 'Reservation annulee par l\'hote. Remboursement integral initie.',
            'reservation' => $reservation->fresh(),
        ]);
    }

    /**
     * Traiter un remboursement integral
     */
    private function processRefund(Reservation $reservation): void
    {
        if (!$reservation->payment_intent_id || $reservation->refunded_at || $reservation->payment_distributed) {
            return;
        }

        try {
            $reservation->status = ReservationStatus::REFUND_INITIATED;
            $reservation->save();

            $refund = $this->stripe->refundPaymentIntent($reservation->payment_intent_id);

            $reservation->refund_id = $refund->id ?? null;
            $reservation->refund_status = 'initiated';
            $reservation->refund_timestamp = now();
            $reservation->save();

            // Le webhook charge.refunded confirmera le remboursement
        } catch (\Throwable $e) {
            Log::error('Erreur remboursement', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            $reservation->status = ReservationStatus::REFUND_FAILED;
            $reservation->refund_failure_reason = $e->getMessage();
            $reservation->save();
        }
    }

    /**
     * Verification des regles de privatisation
     */
    private function checkPrivatisation(Request $request)
    {
        $menuPrestation = MenuPrestation::findOrFail($request->menu_prestation_id);
        $prestationId = $menuPrestation->prestation_id;
        $isPrivate = filter_var($request->input('is_private', false), FILTER_VALIDATE_BOOLEAN);

        // Verifier si prestation deja privatisee
        $activeStatuses = array_map(fn($s) => $s->value, ReservationStatus::activeStatuses());
        $activeStatuses = array_merge($activeStatuses, [
            ReservationStatus::CONFIRMED->value,
            ReservationStatus::COMPLETED_PENDING_OTP->value,
        ]);

        $hasPrivateAccepted = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                $q->where('prestation_id', $prestationId);
            })
            ->where('is_private', true)
            ->whereIn('status', $activeStatuses)
            ->exists();

        if ($hasPrivateAccepted) {
            return response()->json([
                'status' => false,
                'message' => 'Cette prestation est deja privatisee et ne peut plus etre reservee.',
            ], 409);
        }

        if ($isPrivate) {
            $existingReservations = Reservation::whereHas('menuPrestation', function ($q) use ($prestationId) {
                    $q->where('prestation_id', $prestationId);
                })
                ->whereIn('status', $activeStatuses)
                ->count();

            if ($existingReservations > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'La privatisation est impossible : des reservations existent deja.',
                ], 409);
            }
        }

        return null;
    }
}
