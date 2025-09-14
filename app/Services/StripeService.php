<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Créer un PaymentIntent
     */
    public function createPaymentIntent($amount, $currency = 'eur', $chefStripeId = null, $fee = 100)
    {
        return $this->stripe->paymentIntents->create([
            'amount' => $amount * 100, // convertir en centimes
            'currency' => $currency,
            'payment_method_types' => ['card'],
            'application_fee_amount' => $fee, // commission de la plateforme
            'transfer_data' => [
                'destination' => $chefStripeId, // compte connect du chef
            ],
        ]);
    }

    /**
     * Créer un compte Connect (chef)
     */
    public function createConnectAccount($email)
    {
        return $this->stripe->accounts->create([
            'type' => 'express',
            'country' => 'FR', // ou SN pour Sénégal si supporté
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
    }

    /**
     * Générer un onboarding link pour un compte Connect
     */
    public function createAccountLink($accountId, $refreshUrl, $returnUrl)
    {
        return $this->stripe->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }
}
