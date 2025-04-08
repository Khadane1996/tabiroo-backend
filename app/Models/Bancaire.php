<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bancaire extends Model
{
    use HasFactory;
    public $table = 'bancaires';

    protected $fillable = [
        'user_id',
        'iban',
        'bic'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
