<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ProfileCompletionService;

class EnsureProfileComplete
{
    /**
     * Middleware to ensure user profile is 100% complete
     * 
     * Jika profile < 100% complete, return error dan instruksi lengkapi profil
     * Block akses ke consultation, appointment, dan fitur sensitif lainnya
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Skip untuk admin dan guest
        if (!$user || $user->role === 'admin') {
            return $next($request);
        }
        
        // Get profile completion status
        $completion = ProfileCompletionService::getCompletion($user);
        
        // Jika kurang dari 100%, return error
        if ($completion['percentage'] < 100) {
            return response()->json([
                'success' => false,
                'code' => 'PROFILE_INCOMPLETE',
                'message' => 'Profil Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.',
                'data' => [
                    'profile_completion' => $completion,
                    'redirect_to' => $user->role === 'dokter' ? '/dokter/profile' : '/profile'
                ]
            ], 403);
        }
        
        return $next($request);
    }
}
