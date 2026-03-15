<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoValidateReservations extends Command
{
    protected $signature = 'reservations:auto-validate-otp';
    protected $description = 'Valider automatiquement les reservations dont l\'OTP n\'a pas ete saisi sous 24h';

    public function handle(): void
    {
        $reservations = Reservation::expiredOtp()->get();

        $this->info("Reservations en attente OTP expirees: {$reservations->count()}");

        foreach ($reservations as $reservation) {
            try {
                $reservation->status = ReservationStatus::COMPLETED_AUTO_VALIDATED;
                $reservation->auto_validated_at = now();
                $reservation->save();

                $this->info("Reservation #{$reservation->id} -> auto-validee (OTP expire)");
            } catch (\Throwable $e) {
                Log::error('Erreur auto-validation OTP', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
