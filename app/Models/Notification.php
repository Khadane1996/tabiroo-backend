<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;


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
        // $id = chef_id
        $reservation = Reservation::with('client')->find($reservation_id);
        $clientName = $reservation && $reservation->client
            ? ($reservation->client->firstNameOrPseudo ?? 'Un client')
            : 'Un client';

        return self::create([
            'user_id' => $id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Une nouvelle réservation a été faite par $clientName.",
        ]);
    }

    public static function notifyReservationAnnuler($chef_id, $client_id, $reservation_id)
    {
        $client = User::find($client_id);
        $clientName = $client && $client->firstNameOrPseudo
            ? $client->firstNameOrPseudo
            : 'Le client';

        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'annulation',
            'date_notification' => now()->startOfDay()->toISOString(),
            'heure_notification' => now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "$clientName a annulé la réservation #$reservation_id.",
        ]);
    }

    public static function notifyPaymentDistributed($chef_id, $reservation_id)
    {
        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'paiement_distribue',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Le paiement de la réservation #' . $reservation_id . ' a été transféré sur votre compte',
        ]);
    }

    /**
     * Notifier le client avec l'adresse du chef après une réservation
     */
    public static function notifyChefAddressToClient(?User $chef, Reservation $reservation)
    {
        if (!$chef) {
            return null;
        }

        $adresse = $chef->adresse;
        $adresseTexte = $adresse
            ? trim(
                ($adresse->adresse ?? '') . ', ' .
                ($adresse->codePostal ?? '') . ' ' .
                ($adresse->ville ?? '')
            )
            : 'Adresse non renseignée';

        // Afficher uniquement le prénom / pseudo, pas le nom de famille
        $chefName = $chef->firstNameOrPseudo ?: 'votre chef';

        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'adresse_chef',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Voici l'adresse de $chefName pour votre réservation #{$reservation->id} : $adresseTexte",
        ]);
    }

    /**
     * Notification au client : réservation acceptée par le chef
     */
    public static function notifyReservationAcceptedForClient(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_validee_client',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Votre réservation #{$reservation->id} a été acceptée par votre chef.",
        ]);
    }

    /**
     * Notification au chef : réservation marquée comme validée
     */
    public static function notifyReservationAcceptedForChef(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->chef_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_validee_chef',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Vous avez accepté la réservation #{$reservation->id}.",
        ]);
    }

    /**
     * Notification au client : réservation annulée
     */
    public static function notifyReservationCancelledForClient(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_annulee_client',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Votre réservation #{$reservation->id} a été annulée.",
        ]);
    }

}
