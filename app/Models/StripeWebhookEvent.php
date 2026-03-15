<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public static function isAlreadyProcessed(string $stripeEventId): bool
    {
        return self::where('stripe_event_id', $stripeEventId)
            ->whereNotNull('processed_at')
            ->exists();
    }

    public static function record(string $stripeEventId, string $type, ?array $payload = null): self
    {
        return self::create([
            'stripe_event_id' => $stripeEventId,
            'type' => $type,
            'payload' => $payload,
            'processed_at' => now(),
        ]);
    }
}
