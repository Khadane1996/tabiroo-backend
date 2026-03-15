<?php

namespace App\Enums;

enum ReservationStatus: string
{
    // A) Creation / pre-paiement
    case DRAFT = 'draft';

    // B) Reservation a validation manuelle
    case PENDING_HOST_RESPONSE = 'pending_host_response';
    case DECLINED_BY_HOST = 'declined_by_host';
    case CANCELLED_NO_RESPONSE = 'cancelled_no_response';

    // C) Paiement capture / reservation confirmee
    case PAYMENT_CAPTURED_HELD = 'payment_captured_held';
    case CONFIRMED = 'confirmed';

    // D) Prestation terminee / attente OTP
    case COMPLETED_PENDING_OTP = 'completed_pending_otp';
    case COMPLETED_VALIDATED = 'completed_validated';
    case COMPLETED_AUTO_VALIDATED = 'completed_auto_validated';

    // E) Reversement hote
    case PAYOUT_INITIATED = 'payout_initiated';
    case PAYOUT_COMPLETED = 'payout_completed';

    // F) Annulations et remboursements
    case CANCELLED_BY_GUEST_BEFORE_48H = 'cancelled_by_guest_before_48h';
    case CANCELLED_BY_GUEST_AFTER_48H = 'cancelled_by_guest_after_48h';
    case CANCELLED_BY_HOST = 'cancelled_by_host';
    case REFUND_INITIATED = 'refund_initiated';
    case REFUNDED = 'refunded';
    case REFUND_FAILED = 'refund_failed';

    // G) Incident
    case INCIDENT_OPEN = 'incident_open';
    case INCIDENT_RESOLVED = 'incident_resolved';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING_HOST_RESPONSE => 'En attente de l\'hote',
            self::DECLINED_BY_HOST => 'Refusee par l\'hote',
            self::CANCELLED_NO_RESPONSE => 'Annulee (pas de reponse)',
            self::PAYMENT_CAPTURED_HELD => 'Paiement capture',
            self::CONFIRMED => 'Confirmee',
            self::COMPLETED_PENDING_OTP => 'En attente de validation OTP',
            self::COMPLETED_VALIDATED => 'Validee (OTP)',
            self::COMPLETED_AUTO_VALIDATED => 'Validee automatiquement',
            self::PAYOUT_INITIATED => 'Reversement initie',
            self::PAYOUT_COMPLETED => 'Reversement effectue',
            self::CANCELLED_BY_GUEST_BEFORE_48H => 'Annulee par le convive (> 48h)',
            self::CANCELLED_BY_GUEST_AFTER_48H => 'Annulee par le convive (< 48h)',
            self::CANCELLED_BY_HOST => 'Annulee par l\'hote',
            self::REFUND_INITIATED => 'Remboursement initie',
            self::REFUNDED => 'Remboursee',
            self::REFUND_FAILED => 'Remboursement echoue',
            self::INCIDENT_OPEN => 'Incident ouvert',
            self::INCIDENT_RESOLVED => 'Incident resolu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => '#6c757d',
            self::PENDING_HOST_RESPONSE => '#ffc107',
            self::DECLINED_BY_HOST, self::CANCELLED_NO_RESPONSE => '#dc3545',
            self::PAYMENT_CAPTURED_HELD => '#17a2b8',
            self::CONFIRMED => '#28a745',
            self::COMPLETED_PENDING_OTP => '#fd7e14',
            self::COMPLETED_VALIDATED, self::COMPLETED_AUTO_VALIDATED => '#20c997',
            self::PAYOUT_INITIATED => '#6610f2',
            self::PAYOUT_COMPLETED => '#198754',
            self::CANCELLED_BY_GUEST_BEFORE_48H, self::CANCELLED_BY_GUEST_AFTER_48H => '#e74c3c',
            self::CANCELLED_BY_HOST => '#c0392b',
            self::REFUND_INITIATED => '#f39c12',
            self::REFUNDED => '#27ae60',
            self::REFUND_FAILED => '#e74c3c',
            self::INCIDENT_OPEN => '#dc3545',
            self::INCIDENT_RESOLVED => '#6c757d',
        };
    }

    /**
     * Statuts consideres comme "actifs" (reservation en cours)
     */
    public static function activeStatuses(): array
    {
        return [
            self::PENDING_HOST_RESPONSE,
            self::PAYMENT_CAPTURED_HELD,
            self::CONFIRMED,
            self::COMPLETED_PENDING_OTP,
        ];
    }

    /**
     * Statuts consideres comme "termines" (prestation realisee)
     */
    public static function completedStatuses(): array
    {
        return [
            self::COMPLETED_VALIDATED,
            self::COMPLETED_AUTO_VALIDATED,
            self::PAYOUT_INITIATED,
            self::PAYOUT_COMPLETED,
        ];
    }

    /**
     * Statuts consideres comme "annules"
     */
    public static function cancelledStatuses(): array
    {
        return [
            self::DECLINED_BY_HOST,
            self::CANCELLED_NO_RESPONSE,
            self::CANCELLED_BY_GUEST_BEFORE_48H,
            self::CANCELLED_BY_GUEST_AFTER_48H,
            self::CANCELLED_BY_HOST,
            self::REFUND_INITIATED,
            self::REFUNDED,
            self::REFUND_FAILED,
        ];
    }

    /**
     * Statuts ouvrant droit a un remboursement
     */
    public static function refundableStatuses(): array
    {
        return [
            self::CANCELLED_BY_GUEST_BEFORE_48H,
            self::CANCELLED_BY_HOST,
        ];
    }
}
