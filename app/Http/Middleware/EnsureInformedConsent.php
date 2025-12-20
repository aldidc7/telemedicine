<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\ConsentRecord;

/**
 * ============================================
 * MIDDLEWARE - ENSURE INFORMED CONSENT ACCEPTED
 * ============================================
 * 
 * Middleware ini memastikan user telah accept informed consent
 * sebelum mengakses protected resources.
 * 
 * Penempatan di route:
 * Route::middleware(['auth:sanctum', 'ensure-consent'])->group(function () {
 *     // Protected routes
 * });
 * 
 * Routes yang PERLU consent:
 * - GET /api/v1/dashboard (atau endpoint dashboard apapun)
 * - POST /api/v1/konsultasi (create consultation)
 * - PUT /api/v1/pasien/{id} (update profile)
 * - etc
 */
class EnsureInformedConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Admin tidak perlu consent check
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if user has accepted required consents
        $hasTelemedicineConsent = ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', 'telemedicine')
            ->where('accepted', true)
            ->whereNull('revoked_at')
            ->exists();

        if (!$hasTelemedicineConsent) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus accept informed consent sebelum melanjutkan',
                'error_code' => 'CONSENT_REQUIRED',
                'action' => 'Silakan buka settings dan accept informed consent',
            ], 403);
        }

        return $next($request);
    }
}
