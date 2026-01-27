<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDokterVerified
{
    /**
     * Middleware untuk memastikan dokter sudah diverifikasi admin
     * Jika belum, redirect/return error dan instruksi upload dokumen
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->role === 'dokter') {
            $dokter = $user->dokter ?? null;
            if ($dokter && !$dokter->is_verified) {
                return response()->json([
                    'success' => false,
                    'code' => 'DOKTER_NOT_VERIFIED',
                    'message' => 'Akun Anda belum diverifikasi admin. Silakan upload dokumen verifikasi (SIP/KTP) dan tunggu proses verifikasi.',
                    'data' => [
                        'redirect_to' => '/dokter/upload-dokumen',
                        'is_verified' => false
                    ]
                ], 403);
            }
        }
        return $next($request);
    }
}
