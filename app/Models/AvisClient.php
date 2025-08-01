<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvisClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'client_id',
        'note_client',
        'commentaire'
    ];


    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
