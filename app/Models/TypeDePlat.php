<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDePlat extends Model
{
    use HasFactory;

    protected $table = 'types_de_plat';

    protected $fillable = [
        'description'
    ];
}
