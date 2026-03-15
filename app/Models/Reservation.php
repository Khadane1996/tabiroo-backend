<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_prestation_id',
        'client_id',
        'chef_id',
        'sous_total',
        'frais_service',
        'nombre_convive',
        'date_prestation',
        'transaction_detail',
        'payment_intent_id',
        'stripe_charge_id',
        'stripe_balance_transaction_id',
        'payment_distributed',
        'transfer_id',
        'chef_amount',
        'commission_amount',
        'stripe_fee_amount',
        'total_charged',
        'distributed_at',
        'payout_initiated_at',
        'payout_completed_at',
        'motif',
        'status',
        'capture_method',
        'flow_type',
        'host_response_deadline',
        'otp_deadline',
        'auto_validated_at',
        'is_private',
        'private_message',
        'validation_code',
        'validation_code_used_at',
        'refund_id',
        'refund_status',
        'refund_reason',
        'refund_timestamp',
        'refund_failure_reason',
        'refunded_at',
        'auto_cancelled_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'status' => ReservationStatus::class,
        'payment_distributed' => 'boolean',
        'distributed_at' => 'datetime',
        'payout_initiated_at' => 'datetime',
        'payout_completed_at' => 'datetime',
        'chef_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'stripe_fee_amount' => 'decimal:2',
        'total_charged' => 'decimal:2',
        'is_private' => 'boolean',
        'validation_code_used_at' => 'datetime',
        'host_response_deadline' => 'datetime',
        'otp_deadline' => 'datetime',
        'auto_validated_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_timestamp' => 'datetime',
        'auto_cancelled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = ['status_label', 'status_color'];

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Inconnu';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? '#6c757d';
    }

    // Relations

    public function menuPrestation()
    {
        return $this->belongsTo(MenuPrestation::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->whereIn('status', ReservationStatus::activeStatuses());
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ReservationStatus::completedStatuses());
    }

    public function scopeCancelled($query)
    {
        return $query->whereIn('status', ReservationStatus::cancelledStatuses());
    }

    public function scopePendingHostResponse($query)
    {
        return $query->where('status', ReservationStatus::PENDING_HOST_RESPONSE);
    }

    public function scopePendingOtp($query)
    {
        return $query->where('status', ReservationStatus::COMPLETED_PENDING_OTP);
    }

    public function scopePendingPayout($query)
    {
        return $query->whereIn('status', [
            ReservationStatus::COMPLETED_VALIDATED,
            ReservationStatus::COMPLETED_AUTO_VALIDATED,
        ])->where('payment_distributed', false);
    }

    public function scopeExpiredHostResponse($query)
    {
        return $query->where('status', ReservationStatus::PENDING_HOST_RESPONSE)
            ->where('host_response_deadline', '<=', now());
    }

    public function scopeExpiredOtp($query)
    {
        return $query->where('status', ReservationStatus::COMPLETED_PENDING_OTP)
            ->where('otp_deadline', '<=', now());
    }

    // Helpers

    public function isRefundable(): bool
    {
        return in_array($this->status, ReservationStatus::refundableStatuses())
            && $this->payment_intent_id
            && !$this->payment_distributed
            && !$this->refunded_at;
    }

    public function canBeCancelledByGuest(): bool
    {
        return in_array($this->status, [
            ReservationStatus::CONFIRMED,
            ReservationStatus::PAYMENT_CAPTURED_HELD,
        ]);
    }

    public function canBeCancelledByHost(): bool
    {
        return in_array($this->status, [
            ReservationStatus::CONFIRMED,
            ReservationStatus::PAYMENT_CAPTURED_HELD,
            ReservationStatus::PENDING_HOST_RESPONSE,
        ]);
    }

    public function isBeforePrestationDeadline(int $hours = 48): bool
    {
        $datePrestation = $this->parseDatePrestation();
        if (!$datePrestation) {
            return false;
        }
        return now()->diffInHours($datePrestation, false) >= $hours;
    }

    public function parseDatePrestation(): ?\Carbon\Carbon
    {
        if (!$this->date_prestation) {
            return null;
        }
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $this->date_prestation, $m)) {
                return \Carbon\Carbon::parse($m[0])->startOfDay();
            }
            return \Carbon\Carbon::parse($this->date_prestation);
        } catch (\Exception $e) {
            return null;
        }
    }
}
