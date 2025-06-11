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
        'nombre_convive'
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
}
