<?php

namespace App\Http\Controllers;

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

    // Créer un PaymentIntent
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

    // Créer un compte connect
    public function createAccount(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $account = $this->stripe->createConnectAccount($request->email);

        return response()->json($account);
    }

    // Générer un lien d'onboarding
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

        // 🔥 Ajoute ce log
        Log::info('Webhook Stripe reçu', ['type' => $event->type]);

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            Log::info('Paiement réussi', ['id' => $paymentIntent->id]);
            // 👉 ici tu mets à jour ta réservation comme "payée"
        }

        return response()->json(['status' => 'success']);
    }

}
