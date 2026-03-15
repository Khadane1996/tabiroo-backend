<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Notification;
use App\Models\Reservation;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireManualReservations extends Command
{
    protected $signature = 'reservations:expire-manual';
    protected $description = 'Annuler les reservations manuelles sans reponse hote apres 4h (CDC Flux 2)';

    public function handle(StripeService $stripe): void
    {
        $reservations = Reservation::expiredHostResponse()->get();

        $this->info("Reservations manuelles expirees trouvees: {$reservations->count()}");

        foreach ($reservations as $reservation) {
            try {
                // Annuler le PaymentIntent (pas de debit)
                if ($reservation->payment_intent_id) {
                    $stripe->cancelPaymentIntent($reservation->payment_intent_id);
                }

                $reservation->status = ReservationStatus::CANCELLED_NO_RESPONSE;
                $reservation->auto_cancelled_at = now();
                $reservation->cancelled_at = now();
                $reservation->cancelled_by = 'system';
                $reservation->motif = $reservation->motif ?: 'Annulation automatique (pas de reponse hote sous 4h)';
                $reservation->save();

                // Notifier
                Notification::notifyReservationExpired($reservation->chef_id, $reservation->id);
                Notification::notifyReservationCancelledForClient($reservation);

                $this->info("Reservation #{$reservation->id} annulee (silence hote > 4h)");
            } catch (\Throwable $e) {
                Log::error('Erreur expiration reservation manuelle', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Erreur reservation #{$reservation->id}: {$e->getMessage()}");
            }
        }
    }
}
