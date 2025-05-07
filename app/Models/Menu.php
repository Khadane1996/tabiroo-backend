<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    public $table = 'menus';

    protected $fillable = [
        'user_id',
        'photo_url',
        'nom',
        'bioMenu',
        'prix',
        'plat_ids'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'menu_plat');
    }

    public function prestations()
    {
        return $this->belongsToMany(Prestation::class);
    }
    
}
