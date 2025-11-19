<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class StripeController extends Controller
{
    protected $stripe;

    public function __construct(StripeService $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * Créer un PaymentIntent
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'chef_stripe_account_id' => 'required|string',
        ]);

        $paymentIntent = $this->stripe->createPaymentIntent(
            $request->amount,
            'eur',
            $request->chef_stripe_account_id
        );

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    /**
     * Créer un compte connect et le lier au chef (user)
     */
    public function createAccount(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // 1. Créer le compte Stripe Connect
        $account = $this->stripe->createConnectAccount($request->email);

        // 2. Lier le compte au user connecté
        $user = $request->user(); // ⚡ si tu utilises sanctum/jwt
        $user->stripe_account_id = $account->id;
        $user->save();

        return response()->json([
            'message' => 'Compte Stripe Connect créé et lié avec succès',
            'account_id' => $account->id,
        ]);
    }

    /**
     * Générer un lien d'onboarding Stripe
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
     * Gérer les webhooks Stripe
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

        Log::info('Webhook Stripe reçu', ['type' => $event->type]);

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            Log::info('Paiement réussi', ['id' => $paymentIntent->id]);
            // 👉 ici : update réservation / commande = payée
        }

        return response()->json(['status' => 'success']);
    }

    public function checkAccountStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'status' => 'no_account',
                'message' => 'Aucun compte Stripe lié à cet utilisateur'
            ], 400);
        }

        try {
            $account = $this->stripe->retrieveAccount($user->stripe_account_id);

            return response()->json([
                'id' => $account->id,
                'email' => $account->email,
                'type' => $account->type,
                'country' => $account->country,
                'business_type' => $account->business_type,
                'details_submitted' => $account->details_submitted,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'capabilities' => $account->capabilities,
                'individual' => $account->individual,
                'company' => $account->company,
                'external_accounts' => $account->external_accounts,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération compte Stripe', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de récupérer le compte Stripe'
            ], 500);
        }
    }

    /**
     * Créer un SetupIntent et renvoyer le client_secret
     */
    public function createSetupIntent(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            // S'assurer que le client a un Stripe Customer pour pouvoir réutiliser les cartes
            if (!$user->stripe_customer_id) {
                $name = trim(($user->firstNameOrPseudo ?? '') . ' ' . ($user->lastName ?? '')) ?: null;
                $customer = $this->stripe->createCustomer($user->email, $name);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            $setupIntent = $this->stripe->createSetupIntent($user->stripe_customer_id);
            return response()->json(['client_secret' => $setupIntent->client_secret]);
        } catch (\Exception $e) {
            Log::error('Erreur création SetupIntent', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => "Impossible de créer un SetupIntent",
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un PaymentIntent
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
                    'created' => date('Y-m-d H:i:s', $paymentIntent->created)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

}
