<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Notification;
use App\Models\Reservation;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPayouts extends Command
{
    protected $signature = 'reservations:process-payouts';
    protected $description = 'Traiter les reversements aux hotes pour les reservations validees';

    public function handle(StripeService $stripe): void
    {
        $reservations = Reservation::pendingPayout()
            ->with('chef')
            ->get();

        $this->info("Reservations en attente de payout: {$reservations->count()}");

        foreach ($reservations as $reservation) {
            try {
                $chef = $reservation->chef;

                if (!$chef || !$chef->stripe_account_id) {
                    $this->warn("Reservation #{$reservation->id}: chef sans compte Stripe");
                    continue;
                }

                if (!$reservation->payment_intent_id) {
                    $this->warn("Reservation #{$reservation->id}: pas de payment_intent_id");
                    continue;
                }

                $transfer = $stripe->transferToChef(
                    $reservation->payment_intent_id,
                    $chef->stripe_account_id,
                    (float) $reservation->chef_amount,
                    'reservation_' . $reservation->id
                );

                $reservation->payment_distributed = true;
                $reservation->transfer_id = $transfer->id ?? null;
                $reservation->distributed_at = now();
                $reservation->payout_initiated_at = now();
                $reservation->status = ReservationStatus::PAYOUT_INITIATED;
                $reservation->save();

                // Marquer comme complete
                $reservation->payout_completed_at = now();
                $reservation->status = ReservationStatus::PAYOUT_COMPLETED;
                $reservation->save();

                Notification::notifyPaymentDistributed($reservation->chef_id, $reservation->id);

                $this->info("Reservation #{$reservation->id}: payout effectue ({$reservation->chef_amount} EUR)");
            } catch (\Throwable $e) {
                Log::error('Erreur payout reservation', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Reservation #{$reservation->id}: {$e->getMessage()}");
            }
        }
    }
}
