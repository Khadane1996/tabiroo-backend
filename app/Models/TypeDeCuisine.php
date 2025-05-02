<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDeCuisine extends Model
{
    use HasFactory;

    protected $table = 'types_de_cuisine';

    protected $fillable = [
        'description'
    ];
}
