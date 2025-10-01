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
        'payment_intent_id',
        'payment_distributed',
        'transfer_id',
        'chef_amount',
        'commission_amount',
        'distributed_at',
        'motif',
        'status',
    ];

    protected $casts = [
        'payment_distributed' => 'boolean',
        'distributed_at' => 'datetime',
        'chef_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
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
