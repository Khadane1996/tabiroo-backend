<?php

namespace App\Services;

class PaymentCalculationService
{
    // Frais de paiement factures au convive (forfait Tabiroo)
    private const FEE_PERCENTAGE = 0.02;    // 2%
    private const FEE_FIXED = 0.30;          // 0,30 EUR

    // Commission Tabiroo prelevee sur la part de l'hote
    private const COMMISSION_RATE = 0.14;    // 14%

    /**
     * Calculer la ventilation complete d'un paiement
     *
     * @param float $menuPrice Prix unitaire du menu en EUR
     * @param int $nombreConvives Nombre de convives
     * @return array Ventilation detaillee
     */
    public function calculateBreakdown(float $menuPrice, int $nombreConvives): array
    {
        // Etape A: Montant total des menus
        $menusAmount = round($menuPrice * $nombreConvives, 2);

        // Etape B: Frais de paiement (2% + 0,30 EUR)
        $stripeFee = round($menusAmount * self::FEE_PERCENTAGE + self::FEE_FIXED, 2);

        // Etape C: Total paye par le convive
        $totalGuest = round($menusAmount + $stripeFee, 2);

        // Etape D: Commission Tabiroo (14% du montant des menus)
        $commission = round($menusAmount * self::COMMISSION_RATE, 2);

        // Etape E: Montant a reverser a l'hote
        $hostPayout = round($menusAmount - $commission, 2);

        return [
            'menus_amount' => $menusAmount,
            'menu_unit_price' => $menuPrice,
            'nombre_convives' => $nombreConvives,
            'stripe_fee' => $stripeFee,
            'total_guest' => $totalGuest,
            'commission' => $commission,
            'host_payout' => $hostPayout,
            // Montants en centimes pour Stripe
            'total_guest_cents' => (int) round($totalGuest * 100),
            'host_payout_cents' => (int) round($hostPayout * 100),
            'commission_cents' => (int) round($commission * 100),
        ];
    }

    /**
     * Calculer depuis un montant total de menus (pas de prix unitaire)
     */
    public function calculateFromTotal(float $menusAmount): array
    {
        $stripeFee = round($menusAmount * self::FEE_PERCENTAGE + self::FEE_FIXED, 2);
        $totalGuest = round($menusAmount + $stripeFee, 2);
        $commission = round($menusAmount * self::COMMISSION_RATE, 2);
        $hostPayout = round($menusAmount - $commission, 2);

        return [
            'menus_amount' => $menusAmount,
            'stripe_fee' => $stripeFee,
            'total_guest' => $totalGuest,
            'commission' => $commission,
            'host_payout' => $hostPayout,
            'total_guest_cents' => (int) round($totalGuest * 100),
            'host_payout_cents' => (int) round($hostPayout * 100),
            'commission_cents' => (int) round($commission * 100),
        ];
    }
}
