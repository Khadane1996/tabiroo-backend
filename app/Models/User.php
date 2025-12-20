<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstNameOrPseudo',
        'lastName',
        'phone',
        'biographie',
        'photo_url',
        'role_id',
        'confirmation_code',
        'etat',
        'email',
        'password',
        'stripe_account_id',
        'stripe_customer_id',
        'hygiene_qcm_badge_level',
        'hospitalite_qcm_badge_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function adresse()
    {
        return $this->hasOne(Adresse::class);
    }

    public function bancaire()
    {
        return $this->hasOne(Bancaire::class);
    }

    public function prestations()
    {
        return $this->hasMany(Prestation::class);
    }
}
