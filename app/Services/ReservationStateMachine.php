<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;

class ReservationStateMachine
{
    /**
     * Transitions autorisees: [status_actuel => [statuts_cibles_possibles]]
     */
    private const TRANSITIONS = [
        // Flux 1 (auto) + Flux 2 (manual)
        'draft' => [
            'pending_host_response',    // Flux 2
            'payment_captured_held',    // Flux 1
            'cancelled_by_guest_before_48h',
            'cancelled_by_guest_after_48h',
        ],

        // Flux 2: attente reponse hote
        'pending_host_response' => [
            'payment_captured_held',    // Hote accepte -> capture
            'declined_by_host',         // Hote refuse
            'cancelled_no_response',    // Silence > 4h
            'cancelled_by_guest_before_48h',
            'cancelled_by_guest_after_48h',
        ],

        // Paiement capture
        'payment_captured_held' => [
            'confirmed',
        ],

        // Reservation confirmee
        'confirmed' => [
            'completed_pending_otp',
            'cancelled_by_guest_before_48h',
            'cancelled_by_guest_after_48h',
            'cancelled_by_host',
            'incident_open',
        ],

        // Attente OTP
        'completed_pending_otp' => [
            'completed_validated',
            'completed_auto_validated',
            'incident_open',
        ],

        // OTP valide
        'completed_validated' => [
            'payout_initiated',
        ],

        // Auto-validation
        'completed_auto_validated' => [
            'payout_initiated',
        ],

        // Payout
        'payout_initiated' => [
            'payout_completed',
        ],

        // Annulations avec remboursement
        'cancelled_by_guest_before_48h' => [
            'refund_initiated',
        ],
        'cancelled_by_host' => [
            'refund_initiated',
        ],

        // Remboursement
        'refund_initiated' => [
            'refunded',
            'refund_failed',
        ],
        'refund_failed' => [
            'refund_initiated', // retry
            'incident_open',
        ],

        // Incident
        'incident_open' => [
            'incident_resolved',
        ],
        'incident_resolved' => [
            'payout_initiated',
            'refund_initiated',
        ],
    ];

    /**
     * Effectuer une transition de statut
     *
     * @throws \InvalidArgumentException si la transition est illegale
     */
    public function transition(Reservation $reservation, ReservationStatus $newStatus): Reservation
    {
        $currentValue = $reservation->status->value;
        $newValue = $newStatus->value;

        if (!$this->canTransition($reservation->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Transition illegale: {$currentValue} -> {$newValue}"
            );
        }

        $reservation->status = $newStatus;
        $reservation->save();

        return $reservation;
    }

    /**
     * Verifier si une transition est autorisee
     */
    public function canTransition(ReservationStatus $from, ReservationStatus $to): bool
    {
        $allowed = self::TRANSITIONS[$from->value] ?? [];
        return in_array($to->value, $allowed);
    }

    /**
     * Obtenir les transitions possibles depuis un statut donne
     */
    public function getAllowedTransitions(ReservationStatus $current): array
    {
        $values = self::TRANSITIONS[$current->value] ?? [];
        return array_map(fn(string $v) => ReservationStatus::from($v), $values);
    }
}
