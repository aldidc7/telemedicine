<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register rate limiting middleware globally for API
        $middleware->api(append: [
            \App\Http\Middleware\ApiRateLimiter::class,
            \App\Http\Middleware\ValidateFileUpload::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);

        // Register custom middleware untuk role-based access
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
            'dokter' => \App\Http\Middleware\EnsureDokterRole::class,
            'pasien' => \App\Http\Middleware\EnsurePasienRole::class,
            'role' => \App\Http\Middleware\EnsureRoleInList::class,
            'throttle' => \App\Http\Middleware\ApiRateLimiter::class,
            'validate-upload' => \App\Http\Middleware\ValidateFileUpload::class,
            'security-headers' => \App\Http\Middleware\AddSecurityHeaders::class,
            'performance' => \App\Http\Middleware\PerformanceMiddleware::class,
        ]);

        // Redirect unauthenticated API requests to 401 instead of /login
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
