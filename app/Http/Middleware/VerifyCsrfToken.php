<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * Los URIs que no deben ser verificadas para CSRF.
     *
     * @var array
     */
    protected $except = [
        'api/*', // Deshabilitar CSRF para todas las rutas que comienzan con /api/
        'auth/login', // Deshabilitar CSRF para la ruta de login
        'auth/logout', // Deshabilitar CSRF para la ruta de logout
        'sanctum/csrf-cookie', // Deshabilitar CSRF para la ruta de CSRF
    ];
}
