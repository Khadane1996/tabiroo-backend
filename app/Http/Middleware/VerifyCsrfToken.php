<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Les routes exclues de la vérification CSRF (utile pour les API POST).
     *
     * @var array<int, string>
     */
    protected $except = [
        // Exemple : 'api/*'
    ];
}
