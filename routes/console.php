<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Reservation;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Annuler automatiquement les réservations non acceptées après 48h
Artisan::command('reservations:auto-cancel', function () {
    $this->info('Recherche des réservations à annuler automatiquement...');

    $threshold = now()->subHours(48);

    $reservations = Reservation::where('status', 'pending')
        ->whereNotNull('payment_intent_id')
        ->whereNull('refunded_at')
        ->where('created_at', '<=', $threshold)
        ->get();

    /** @var StripeService $stripe */
    $stripe = app(StripeService::class);

    foreach ($reservations as $reservation) {
        try {
            $this->info('Annulation de la réservation #'.$reservation->id);

            $stripe->refundPaymentIntent($reservation->payment_intent_id);
            $reservation->status = 'cancelled';
            $reservation->motif = $reservation->motif ?: 'Annulation automatique (48h sans acceptation)';
            $reservation->refunded_at = now();
            $reservation->auto_cancelled_at = now();
            $reservation->save();
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'annulation automatique de la réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    $this->info('Traitement des réservations en attente terminé.');
})->purpose('Annuler automatiquement les réservations non acceptées après 48h');
