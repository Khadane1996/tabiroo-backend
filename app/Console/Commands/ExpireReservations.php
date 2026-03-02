<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Notification;
use Carbon\Carbon;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Expire les réservations en attente depuis plus de 2 heures';

    public function handle()
    {
        $twoHoursAgo = Carbon::now()->subHours(2);

        $expiredReservations = Reservation::where('status', 'pending')
            ->where('created_at', '<=', $twoHoursAgo)
            ->get();

        foreach ($expiredReservations as $reservation) {
            $reservation->update([
                'status' => 'expired',
                'auto_cancelled_at' => now(),
            ]);

            // Notifier le chef
            Notification::notifyReservationExpired(
                $reservation->chef_id,
                $reservation->id
            );

            // Notifier le client
            Notification::create([
                'user_id' => $reservation->client_id,
                'reservation_id' => $reservation->id,
                'type' => 'reservation_expired',
                'date_notification' => Carbon::now()->toDateString(),
                'heure_notification' => Carbon::now()->toTimeString(),
                'etat' => 0,
                'commentaire' => 'Votre réservation a expiré car l\'hôte n\'a pas répondu dans les délais.',
            ]);
        }

        $this->info("{$expiredReservations->count()} réservation(s) expirée(s).");
    }
}
