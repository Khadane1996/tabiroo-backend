<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use App\Services\ExpoNotificationService;


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

    /**
     * Boot : envoyer automatiquement une notification push Expo
     * à chaque création d'une notification en base.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            try {
                ExpoNotificationService::sendToUser(
                    $notification->user_id,
                    self::getTitleForType($notification->type),
                    $notification->commentaire,
                    [
                        'type' => $notification->type,
                        'reservation_id' => $notification->reservation_id,
                    ]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Push notification failed', [
                    'user_id' => $notification->user_id,
                    'type' => $notification->type,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Mapping type → titre pour les notifications push
     */
    private static function getTitleForType(string $type): string
    {
        $titles = [
            'reservation_pending' => 'Nouvelle réservation',
            'reservation_auto_confirmed' => 'Réservation confirmée',
            'reservation_validated' => 'Réservation confirmée',
            'reservation_refused' => 'Réservation refusée',
            'reservation_cancelled_by_client' => 'Réservation annulée',
            'reservation_expired' => 'Réservation expirée',
            'prestation_created' => 'Prestation créée',
            'prestation_updated' => 'Prestation mise à jour',
            'prestation_cancelled' => 'Prestation annulée',
            'prestation_imminent' => 'Prestation imminente',
            'prestation_complete' => 'Prestation complète',
            'payment_triggered' => 'Paiement déclenché',
            'new_comment' => 'Nouveau commentaire',
            'profile_incomplete' => 'Profil incomplet',
        ];

        return $titles[$type] ?? 'Tabiroo';
    }

    // L'utilisateur auquel appartient la notification
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Anciennes méthodes (conservées pour compatibilité) ---

    public static function notifyReservation($id, $reservation_id)
    {
        $reservation = Reservation::with('client')->find($reservation_id);
        $clientName = $reservation && $reservation->client
            ? ($reservation->client->firstNameOrPseudo ?? 'Un convive')
            : 'Un convive';

        return self::create([
            'user_id' => $id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation_pending',
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
            : 'Le convive';

        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation_cancelled_by_client',
            'date_notification' => now()->toDateString(),
            'heure_notification' => now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "$clientName a annulé sa réservation.",
        ]);
    }

    public static function notifyPaymentDistributed($chef_id, $reservation_id)
    {
        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'payment_triggered',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Le paiement de votre prestation a été transféré sur votre compte.',
        ]);
    }

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

        $chefName = $chef->firstNameOrPseudo ?: 'votre hôte';

        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_validated',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Voici l'adresse de $chefName pour votre réservation : $adresseTexte",
        ]);
    }

    public static function notifyReservationAcceptedForClient(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_validated',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Votre réservation a été acceptée par votre hôte.",
        ]);
    }

    public static function notifyReservationAcceptedForChef(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->chef_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_validated',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Vous avez accepté une réservation.",
        ]);
    }

    public static function notifyReservationCancelledForClient(Reservation $reservation)
    {
        return self::create([
            'user_id' => $reservation->client_id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_refused',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Votre réservation a été refusée.",
        ]);
    }

    // --- Nouvelles méthodes pour les types spécifiques ---

    public static function notifyAutoConfirmed($chef_id, $reservation_id)
    {
        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation_auto_confirmed',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Une réservation a été automatiquement confirmée.',
        ]);
    }

    public static function notifyReservationExpired($chef_id, $reservation_id)
    {
        return self::create([
            'user_id' => $chef_id,
            'reservation_id' => $reservation_id,
            'type' => 'reservation_expired',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Une réservation a expiré car elle n\'a pas été traitée dans les délais.',
        ]);
    }

    public static function notifyPrestationCreated($user_id, $prestation_id)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'prestation_created',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Votre prestation a été créée avec succès et est visible par les convives.',
        ]);
    }

    public static function notifyPrestationUpdated($user_id, $prestation_id)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'prestation_updated',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Votre prestation a été mise à jour.',
        ]);
    }

    public static function notifyPrestationCancelled($user_id)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'prestation_cancelled',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Votre prestation a été annulée.',
        ]);
    }

    public static function notifyPrestationImminent($user_id, $prestation_date)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'prestation_imminent',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "Rappel : vous avez une prestation prévue demain ($prestation_date). Préparez-vous !",
        ]);
    }

    public static function notifyPrestationComplete($user_id)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'prestation_complete',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Votre prestation est complète ! Il n\'y a plus de places disponibles.',
        ]);
    }

    public static function notifyNewComment($user_id, $commenter_name)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'new_comment',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => "$commenter_name a laissé un commentaire sur votre prestation.",
        ]);
    }

    public static function notifyProfileIncomplete($user_id)
    {
        return self::create([
            'user_id' => $user_id,
            'type' => 'profile_incomplete',
            'date_notification' => Carbon::now()->toDateString(),
            'heure_notification' => Carbon::now()->toTimeString(),
            'etat' => 0,
            'commentaire' => 'Votre profil est incomplet. Complétez-le pour augmenter vos chances de réservation.',
        ]);
    }

}
