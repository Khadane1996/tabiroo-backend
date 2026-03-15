<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// === CDC Stripe: Commandes schedulees ===

// Expirer les reservations manuelles sans reponse hote apres 4h (Flux 2)
Schedule::command('reservations:expire-manual')->everyFiveMinutes();

// Marquer les prestations terminees -> COMPLETED_PENDING_OTP
Schedule::command('reservations:mark-completed')->everyFifteenMinutes();

// Auto-valider les reservations dont l'OTP n'a pas ete saisi sous 24h
Schedule::command('reservations:auto-validate-otp')->everyFifteenMinutes();

// Traiter les reversements aux hotes (payouts)
Schedule::command('reservations:process-payouts')->everyThirtyMinutes();

// Notifier les hotes dont les prestations sont prevues demain (J-1)
Schedule::command('notifications:imminent-prestations')->dailyAt('18:00');
