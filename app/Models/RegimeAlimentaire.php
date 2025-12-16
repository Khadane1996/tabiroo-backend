<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimeAlimentaire extends Model
{
    use HasFactory;

    protected $table = 'regimes_alimentaire';

    protected $fillable = [
        'description'
    ];

    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'plat_regime_alimentaire');
    }
}
