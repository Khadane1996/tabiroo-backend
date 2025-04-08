<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adresse extends Model
{
    use HasFactory;
    public $table = 'adresses';

    protected $fillable = [
        'user_id',
        'adresse',
        'complementAdresse',
        'codePostal',
        'ville'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
