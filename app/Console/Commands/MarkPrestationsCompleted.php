<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MarkPrestationsCompleted extends Command
{
    protected $signature = 'reservations:mark-completed';
    protected $description = 'Marquer les reservations confirmees comme terminees apres la date/heure de fin de prestation';

    public function handle(): void
    {
        $reservations = Reservation::where('status', ReservationStatus::CONFIRMED)
            ->whereNotNull('date_prestation')
            ->get();

        $now = Carbon::now();
        $marked = 0;

        foreach ($reservations as $reservation) {
            try {
                $datePrestation = $reservation->parseDatePrestation();
                if (!$datePrestation) continue;

                // Considerer la prestation comme terminee si la date est passee
                // (on ajoute un buffer de quelques heures pour la fin de prestation)
                $endTime = $datePrestation->copy()->endOfDay();

                // Recuperer l'heure de fin depuis la prestation liee si possible
                $prestation = $reservation->menuPrestation?->prestation;
                if ($prestation && $prestation->end_time) {
                    try {
                        $endTime = $datePrestation->copy()->setTimeFromTimeString($prestation->end_time);
                    } catch (\Throwable $e) {
                        // Garder endOfDay par defaut
                    }
                }

                if ($now->isAfter($endTime)) {
                    $reservation->status = ReservationStatus::COMPLETED_PENDING_OTP;
                    $reservation->otp_deadline = $endTime->copy()->addHours(24);
                    $reservation->save();
                    $marked++;
                    $this->info("Reservation #{$reservation->id} -> COMPLETED_PENDING_OTP");
                }
            } catch (\Throwable $e) {
                Log::error('Erreur mark-completed', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Reservations marquees comme terminees: {$marked}");
    }
}
