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
     * Créer un PaymentIntent avec destination (alternative recommandée)
     * Cette méthode fait apparaître immédiatement la transaction dans le dashboard du chef
     */
    public function createPaymentIntentWithDestination($amount, $currency = 'eur', $chefStripeId = null, $applicationFee = null)
    {
        $amountCents = intval($amount * 100);
        
        $params = [
            'amount' => $amountCents,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ];
        
        // Utiliser destination charges pour une meilleure visibilité
        if ($chefStripeId) {
            $params['transfer_data'] = [
                'destination' => $chefStripeId,
            ];
            
            // La commission de l'application
            if ($applicationFee) {
                $params['application_fee_amount'] = intval($applicationFee * 100);
            }
        }
        
        return $this->stripe->paymentIntents->create($params);
    }

    /**
     * Créer un compte Connect pour un chef
     */
    public function createConnectAccount($email, $country = 'FR')
    {
        return $this->stripe->accounts->create([
            'type' => 'express',
            'country' => $country,
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
    }

    /**
     * Générer un lien d'onboarding pour un compte Connect
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
    
    public function retrieveAccount($accountId)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret')); // Assure-toi que ta clé est bonne
        return \Stripe\Account::retrieve($accountId);
    }



    /**
     * Créer un SetupIntent pour permettre d'enregistrer une carte
     */
    public function createSetupIntent()
    {
        return $this->stripe->setupIntents->create([
            'payment_method_types' => ['card'],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

}
