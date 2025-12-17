<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use HasFactory;
    public $table = 'prestations';

    protected $fillable = [
        'user_id',
        'type_de_repas',
        'start_time',
        'end_time',
        'date_limite',
        'heure_arrivee_convive',
        'date_prestation',
        'ambianceanimation_id',
        'description_ambiance',
        'hashtags',
        'nombre_convive',
        'choix'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }

    public function typeDeRepas()
    {
        return $this->belongsTo(TypeDeRepas::class, 'type_de_repas');
    }
    
    public function prestations()
    {
        return $this->hasMany(Prestation::class, 'type_de_plat');
    }

    public function ambianceAnimation()
    {
        return $this->belongsTo(AmbianceAnimation::class, 'ambianceanimation_id');
    }

    public function reservationsConfirmées()
    {
        return $this->hasManyThrough(
            Reservation::class,
            MenuPrestation::class,
            'prestation_id', // clé étrangère sur menu_prestation
            'menu_prestation_id', // clé étrangère sur reservations
            'id', // clé locale sur prestations
            'id'  // clé locale sur menu_prestation
        )->whereIn('status', ['pending', 'accepted']);
    }

    protected $appends = ['places_restantes', 'est_privatisee'];

    public function getPlacesRestantesAttribute()
    {
        $reserved = $this->reservationsConfirmées->sum('nombre_convive');
        return max($this->nombre_convive - $reserved, 0);
    }

    public function reservations()
    {
        return $this->hasManyThrough(
            Reservation::class,
            MenuPrestation::class,
            'prestation_id',       // FK dans menu_prestations
            'menu_prestation_id',  // FK dans reservations
            'id',                  // PK local prestations
            'id'                   // PK local menu_prestations
        );
    }

    public function reservationsConfirméesTwo()
    {
        return $this->reservations()->where('status', 'confirmed');
    }

    /**
     * Indique si la prestation est privatisée (au moins une réservation privée acceptée)
     */
    public function getEstPrivatiseeAttribute()
    {
        return $this->reservations()
            ->where('is_private', true)
            ->whereIn('status', ['accepted'])
            ->exists();
    }

}
