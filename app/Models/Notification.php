<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_id',
        'type',
        'date_notification',
        'heure_notification',
        'etat',
        'commentaire',
    ];

    // L'utilisateur auquel appartient la notification
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function notifyReservation($id, $reservation_id)
    {
        return self::create([
            'user_id' => $id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Une nouvelle réservation a été faite par le client #' . $id,
        ]);
    }

    public static function notifyReservationAnnuler($chef_id, $client_id, $reservation_id)
    {
        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'annulation',
            'date_notification' => now()->startOfDay()->toISOString(),
            'heure_notification' => now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Le client #' . $client_id . ' a annulé la réservation #' . $reservation_id,
        ]);
    }

}
