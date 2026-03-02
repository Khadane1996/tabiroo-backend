<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prestation;
use App\Models\Notification;
use Carbon\Carbon;

class NotifyImminentPrestations extends Command
{
    protected $signature = 'notifications:imminent-prestations';
    protected $description = 'Envoie une notification aux hôtes dont les prestations sont prévues demain';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $prestations = Prestation::whereDate('date_prestation', $tomorrow)->get();

        foreach ($prestations as $prestation) {
            // Vérifier qu'on n'a pas déjà notifié
            $exists = Notification::where('user_id', $prestation->user_id)
                ->where('type', 'prestation_imminent')
                ->whereDate('date_notification', Carbon::today())
                ->exists();

            if (!$exists) {
                Notification::notifyPrestationImminent(
                    $prestation->user_id,
                    $prestation->date_prestation
                );
            }
        }

        $this->info("Notifications J-1 envoyées pour {$prestations->count()} prestation(s).");
    }
}
