<?php
// Register custom middleware for doctor verification

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...existing code...

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // ...existing middleware...
        'dokter.verified' => \App\Http\Middleware\EnsureDokterVerified::class,
    ];
}
