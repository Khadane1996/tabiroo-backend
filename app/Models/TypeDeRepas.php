<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDeRepas extends Model
{
    use HasFactory;

    protected $table = 'types_de_repas';

    protected $fillable = [
        'description'
    ];
}
