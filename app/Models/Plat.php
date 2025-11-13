<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    use HasFactory;
    public $table = 'plats';

    protected $fillable = [
        'user_id',
        'photo_url',
        'photo_url_2',
        'photo_url_3',
        'photo_url_4',
        'nom',
        'bioPlat',
        'ingredient',
        'allergene',
        'type_de_plat_id',
        'type_de_cuisine_id',
        'regime_alimentaire_id',
        'theme_culinaire_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeDePlat()
    {
        return $this->belongsTo(TypeDePlat::class);
    }

    public function typeDeCuisine()
    {
        return $this->belongsTo(TypeDeCuisine::class);
    }

    public function regimeAlimentaire()
    {
        return $this->belongsTo(RegimeAlimentaire::class);
    }

    public function themeCulinaire()
    {
        return $this->belongsTo(ThemeCulinaire::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_plat');
    }
}
