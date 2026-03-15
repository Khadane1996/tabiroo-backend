<?php

namespace App\Services;

use App\Models\User;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Creer un PaymentIntent escrow sur le compte plateforme
     * Supporte capture_method: automatic (Flux 1) ou manual (Flux 2)
     */
    public function createEscrowPaymentIntent(
        float $amount,
        string $currency = 'eur',
        ?string $customerId = null,
        array $metadata = [],
        string $captureMethod = 'automatic'
    ) {
        $amountCents = (int) round($amount * 100);

        $params = [
            'amount' => $amountCents,
            'currency' => $currency,
            'payment_method_types' => ['card'],
            'metadata' => $metadata,
            'capture_method' => $captureMethod,
        ];

        if ($customerId) {
            $params['customer'] = $customerId;
        }

        return $this->stripe->paymentIntents->create($params);
    }

    /**
     * Capturer un PaymentIntent prealablement autorise (Flux 2 - manual capture)
     */
    public function capturePaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->capture($paymentIntentId);
    }

    /**
     * Annuler un PaymentIntent (Flux 2 - refus hote ou silence > 4h)
     */
    public function cancelPaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->cancel($paymentIntentId);
    }

    /**
     * Creer un compte Connect Express pour un hote (individual uniquement)
     */
    public function createConnectAccount(User $user, $country = 'FR')
    {
        $email = $user->email;

        return $this->stripe->accounts->create([
            'type'          => 'express',
            'country'       => $country,
            'email'         => $email,
            'business_type' => 'individual',
            'individual'    => [
                'email'      => $email,
                'first_name' => $user->firstNameOrPseudo ?? '',
                'last_name'  => $user->lastName ?? '',
            ],
            'business_profile' => [
                'mcc'                 => '5812',
                'product_description' => 'Plateforme de reservation de chefs a domicile',
                'url'                 => 'https://www.tabiroo.com',
            ],
            'capabilities'  => [
                'card_payments' => ['requested' => true],
                'transfers'     => ['requested' => true],
            ],
        ]);
    }

    /**
     * Generer un lien d'onboarding pour un compte Connect
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

    /**
     * Recuperer les informations d'un compte Connect
     */
    public function retrieveAccount($accountId)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        return \Stripe\Account::retrieve($accountId);
    }

    /**
     * Recuperer le statut normalise d'un compte Connect
     */
    public function getAccountStatus(string $accountId): array
    {
        $account = $this->retrieveAccount($accountId);

        return [
            'id' => $account->id,
            'details_submitted' => (bool) $account->details_submitted,
            'charges_enabled' => (bool) $account->charges_enabled,
            'payouts_enabled' => (bool) $account->payouts_enabled,
            'requirements_currently_due_count' => count($account->requirements->currently_due ?? []),
            'requirements_currently_due' => $account->requirements->currently_due ?? [],
        ];
    }

    /**
     * Creer un SetupIntent pour enregistrer une carte
     */
    public function createSetupIntent(?string $customerId = null)
    {
        $params = [
            'payment_method_types' => ['card'],
        ];

        if ($customerId) {
            $params['customer'] = $customerId;
        }

        return $this->stripe->setupIntents->create($params);
    }

    /**
     * Creer un Customer Stripe
     */
    public function createCustomer(string $email, ?string $name = null)
    {
        $data = ['email' => $email];
        if ($name) {
            $data['name'] = $name;
        }
        return $this->stripe->customers->create($data);
    }

    public function retrievePaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Creer un remboursement integral lie a un PaymentIntent
     */
    public function refundPaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->refunds->create([
            'payment_intent' => $paymentIntentId,
        ]);
    }

    /**
     * Effectuer un virement vers le compte Stripe Connect d'un chef
     */
    public function transferToChef(
        string $paymentIntentId,
        string $chefStripeAccountId,
        float $amount,
        ?string $transferGroup = null
    ) {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
        $amountCents = (int) round($amount * 100);

        $chargeId = $paymentIntent->latest_charge ?? null;

        $params = [
            'amount' => $amountCents,
            'currency' => $paymentIntent->currency,
            'destination' => $chefStripeAccountId,
        ];

        if ($chargeId) {
            $params['source_transaction'] = $chargeId;
        }

        if ($transferGroup) {
            $params['transfer_group'] = $transferGroup;
        }

        return $this->stripe->transfers->create($params);
    }

    /**
     * Creer un PaymentIntent avec destination (legacy - conserve pour compatibilite)
     */
    public function createPaymentIntentWithDestination($amount, $currency = 'eur', $chefStripeId = null, $applicationFee = null, $customerId = null)
    {
        $amountCents = intval($amount * 100);

        $params = [
            'amount' => $amountCents,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ];

        if ($customerId) {
            $params['customer'] = $customerId;
        }

        if ($chefStripeId) {
            $params['transfer_data'] = [
                'destination' => $chefStripeId,
            ];
            if ($applicationFee) {
                $params['application_fee_amount'] = intval($applicationFee * 100);
            }
        }

        return $this->stripe->paymentIntents->create($params);
    }
}
