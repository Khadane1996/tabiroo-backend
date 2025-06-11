<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmbianceAnimation extends Model
{
    use HasFactory;
    public $table = 'ambianceanimations';

    protected $fillable = [
        'description'
    ];
}
