<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPrestation extends Model
{
    use HasFactory;
    public $table = 'menu_prestation';

    protected $fillable = [
        'prestation_id',
        'menu_id',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }

    public function typeDeRepas()
    {
        return $this->belongsTo(TypeDeRepas::class);
    }

}
