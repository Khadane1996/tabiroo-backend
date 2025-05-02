<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeCulinaire extends Model
{
    use HasFactory;

    protected $table = 'themes_culinaire';

    protected $fillable = [
        'description'
    ];
}
