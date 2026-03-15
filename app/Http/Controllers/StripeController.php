<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * Creer un PaymentIntent
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'chef_stripe_account_id' => 'required|string',
        ]);

        $paymentIntent = $this->stripe->createPaymentIntentWithDestination(
            $request->amount,
            'eur',
            $request->chef_stripe_account_id
        );

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    /**
     * Creer un compte Connect et le lier au chef
     */
    public function createAccount(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = $request->user();
        $account = $this->stripe->createConnectAccount($user);

        $user->stripe_account_id = $account->id;
        $user->save();

        // Recuperer et persister le statut initial
        $this->persistAccountStatus($user, $account->id);

        return response()->json([
            'message' => 'Compte Stripe Connect cree et lie avec succes',
            'account_id' => $account->id,
        ]);
    }

    /**
     * Generer un lien d'onboarding Stripe
     */
    public function createAccountLink(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'refresh_url' => 'required|url',
            'return_url' => 'required|url',
        ]);

        $link = $this->stripe->createAccountLink(
            $request->account_id,
            $request->refresh_url,
            $request->return_url
        );

        return response()->json(['url' => $link->url]);
    }

    /**
     * Webhook Stripe - gestion complete CDC
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error('Webhook Stripe invalide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        // Idempotence: verifier si l'event a deja ete traite
        if (StripeWebhookEvent::isAlreadyProcessed($event->id)) {
            Log::info('Webhook Stripe deja traite', ['event_id' => $event->id]);
            return response()->json(['status' => 'already_processed']);
        }

        Log::info('Webhook Stripe recu', [
            'event_id' => $event->id,
            'type' => $event->type,
        ]);

        try {
            match ($event->type) {
                'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
                'charge.refunded' => $this->handleChargeRefunded($event),
                'refund.updated' => $this->handleRefundUpdated($event),
                'account.updated' => $this->handleAccountUpdated($event),
                'charge.dispute.created' => $this->handleDisputeCreated($event),
                default => Log::info('Webhook Stripe non gere', ['type' => $event->type]),
            };
        } catch (\Throwable $e) {
            Log::error('Erreur traitement webhook', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);
        }

        // Enregistrer l'event comme traite
        StripeWebhookEvent::record($event->id, $event->type, [
            'object_id' => $event->data->object->id ?? null,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * A) payment_intent.succeeded
     */
    private function handlePaymentIntentSucceeded($event): void
    {
        $pi = $event->data->object;
        $reservationId = $pi->metadata->reservation_id ?? null;

        if (!$reservationId) {
            Log::warning('payment_intent.succeeded sans reservation_id', ['pi_id' => $pi->id]);
            return;
        }

        $reservation = Reservation::find($reservationId);
        if (!$reservation) {
            Log::warning('Reservation introuvable pour PI', ['reservation_id' => $reservationId]);
            return;
        }

        // Stocker les IDs Stripe pour tracabilite
        $reservation->stripe_charge_id = $pi->latest_charge ?? null;
        $reservation->stripe_balance_transaction_id = null; // sera renseigne si besoin

        // Transition selon le statut actuel
        if (in_array($reservation->status, [ReservationStatus::DRAFT, ReservationStatus::PENDING_HOST_RESPONSE])) {
            $reservation->status = ReservationStatus::PAYMENT_CAPTURED_HELD;
            $reservation->save();
            $reservation->status = ReservationStatus::CONFIRMED;
        }

        $reservation->save();

        Log::info('Reservation confirmee via webhook', [
            'reservation_id' => $reservation->id,
            'pi_id' => $pi->id,
        ]);
    }

    /**
     * B) charge.refunded
     */
    private function handleChargeRefunded($event): void
    {
        $charge = $event->data->object;
        $piId = $charge->payment_intent ?? null;

        if (!$piId) return;

        $reservation = Reservation::where('payment_intent_id', $piId)->first();
        if (!$reservation) return;

        $reservation->status = ReservationStatus::REFUNDED;
        $reservation->refund_status = 'succeeded';
        $reservation->refunded_at = now();
        $reservation->save();

        Log::info('Remboursement confirme via webhook', [
            'reservation_id' => $reservation->id,
        ]);
    }

    /**
     * B bis) refund.updated
     */
    private function handleRefundUpdated($event): void
    {
        $refund = $event->data->object;
        $piId = $refund->payment_intent ?? null;

        if (!$piId) return;

        $reservation = Reservation::where('payment_intent_id', $piId)->first();
        if (!$reservation) return;

        if ($refund->status === 'failed') {
            $reservation->status = ReservationStatus::REFUND_FAILED;
            $reservation->refund_status = 'failed';
            $reservation->refund_failure_reason = $refund->failure_reason ?? 'Inconnu';
            $reservation->save();

            // Creer un incident pour le dashboard admin
            Incident::create([
                'reservation_id' => $reservation->id,
                'type' => 'refund_failed',
                'description' => 'Remboursement echoue: ' . ($refund->failure_reason ?? 'Raison inconnue'),
                'status' => 'open',
            ]);

            Log::error('Remboursement echoue', [
                'reservation_id' => $reservation->id,
                'refund_id' => $refund->id,
            ]);
        } elseif ($refund->status === 'succeeded') {
            $reservation->status = ReservationStatus::REFUNDED;
            $reservation->refund_status = 'succeeded';
            $reservation->refunded_at = now();
            $reservation->save();
        }
    }

    /**
     * C) account.updated - KYC/activation Stripe hote
     */
    private function handleAccountUpdated($event): void
    {
        $account = $event->data->object;
        $accountId = $account->id;

        $user = User::where('stripe_account_id', $accountId)->first();
        if (!$user) {
            Log::warning('User introuvable pour account.updated', ['account_id' => $accountId]);
            return;
        }

        $user->stripe_details_submitted = (bool) $account->details_submitted;
        $user->stripe_charges_enabled = (bool) $account->charges_enabled;
        $user->stripe_payouts_enabled = (bool) $account->payouts_enabled;
        $user->stripe_requirements_currently_due_count = count($account->requirements->currently_due ?? []);
        $user->save();

        Log::info('Compte Stripe hote mis a jour', [
            'user_id' => $user->id,
            'payouts_enabled' => $user->stripe_payouts_enabled,
        ]);
    }

    /**
     * D) charge.dispute.created - Contestation bancaire
     */
    private function handleDisputeCreated($event): void
    {
        $dispute = $event->data->object;
        $chargeId = $dispute->charge ?? null;

        $reservation = $chargeId
            ? Reservation::where('stripe_charge_id', $chargeId)->first()
            : null;

        Incident::create([
            'reservation_id' => $reservation?->id,
            'type' => 'dispute',
            'description' => 'Contestation bancaire recue. Montant: ' . ($dispute->amount / 100) . ' EUR. Raison: ' . ($dispute->reason ?? 'Inconnue'),
            'status' => 'open',
            'stripe_dispute_id' => $dispute->id,
        ]);

        Log::error('Contestation bancaire', [
            'dispute_id' => $dispute->id,
            'reservation_id' => $reservation?->id,
        ]);
    }

    /**
     * Verifier le statut du compte Stripe d'un hote (+ persistance)
     */
    public function checkAccountStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'status' => 'no_account',
                'message' => 'Aucun compte Stripe lie a cet utilisateur',
                'stripe_payouts_enabled' => false,
                'stripe_charges_enabled' => false,
                'stripe_details_submitted' => false,
            ], 200);
        }

        try {
            $accountStatus = $this->stripe->getAccountStatus($user->stripe_account_id);

            // Persister en BDD
            $this->persistAccountStatus($user, $user->stripe_account_id);

            // Determiner le statut UI pour l'app
            $uiStatus = $this->getUiStatus($accountStatus);

            return response()->json(array_merge($accountStatus, [
                'ui_status' => $uiStatus['status'],
                'ui_message' => $uiStatus['message'],
                'can_publish' => $accountStatus['payouts_enabled'],
            ]));
        } catch (\Exception $e) {
            Log::error('Erreur recuperation compte Stripe', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de recuperer le compte Stripe'
            ], 500);
        }
    }

    /**
     * Creer un SetupIntent
     */
    public function createSetupIntent(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifie'], 401);
            }

            if (!$user->stripe_customer_id) {
                $name = trim(($user->firstNameOrPseudo ?? '') . ' ' . ($user->lastName ?? '')) ?: null;
                $customer = $this->stripe->createCustomer($user->email, $name);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            $setupIntent = $this->stripe->createSetupIntent($user->stripe_customer_id);
            return response()->json(['client_secret' => $setupIntent->client_secret]);
        } catch (\Exception $e) {
            Log::error('Erreur creation SetupIntent', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Impossible de creer un SetupIntent'], 500);
        }
    }

    /**
     * Verifier le statut d'un PaymentIntent
     */
    public function checkPaymentStatus(Request $request, $paymentIntentId)
    {
        try {
            $paymentIntent = $this->stripe->retrievePaymentIntent($paymentIntentId);

            return response()->json([
                'status' => true,
                'payment_intent' => [
                    'id' => $paymentIntent->id,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'metadata' => $paymentIntent->metadata,
                    'created' => date('Y-m-d H:i:s', $paymentIntent->created),
                    'capture_method' => $paymentIntent->capture_method,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // ===================== METHODES PRIVEES =====================

    /**
     * Persister le statut Stripe d'un hote en BDD
     */
    private function persistAccountStatus(User $user, string $accountId): void
    {
        try {
            $status = $this->stripe->getAccountStatus($accountId);
            $user->stripe_details_submitted = $status['details_submitted'];
            $user->stripe_charges_enabled = $status['charges_enabled'];
            $user->stripe_payouts_enabled = $status['payouts_enabled'];
            $user->stripe_requirements_currently_due_count = $status['requirements_currently_due_count'];
            $user->save();
        } catch (\Throwable $e) {
            Log::warning('Impossible de persister le statut Stripe', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Mapping statut technique Stripe -> statut UI (CDC section 4.7)
     */
    private function getUiStatus(array $accountStatus): array
    {
        if (!$accountStatus['details_submitted']) {
            return [
                'status' => 'not_activated',
                'message' => 'Activez votre compte de paiement pour publier et recevoir vos revenus.',
            ];
        }

        if ($accountStatus['requirements_currently_due_count'] > 0) {
            return [
                'status' => 'action_required',
                'message' => 'Informations complementaires necessaires pour activer vos paiements.',
            ];
        }

        if (!$accountStatus['payouts_enabled']) {
            return [
                'status' => 'verification_pending',
                'message' => 'Votre compte est en cours de verification.',
            ];
        }

        return [
            'status' => 'activated',
            'message' => 'Compte de paiement active.',
        ];
    }
}
