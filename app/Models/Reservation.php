<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_prestation_id',
        'client_id',
        'chef_id',
        'sous_total',
        'frais_service',
        'nombre_convive',
        'date_prestation',
        'transaction_detail',
        'motif',
        'status',
    ];

    // Menu prestation liée
    public function menuPrestation()
    {
        return $this->belongsTo(MenuPrestation::class);
    }

    // Client (utilisateur ayant fait la réservation)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Chef (utilisateur qui va réaliser la prestation)
    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

}
