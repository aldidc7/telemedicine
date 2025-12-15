<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasienRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized - Token diperlukan'
            ], 401);
        }

        if (Auth::user()->role !== 'pasien') {
            return response()->json([
                'message' => 'Forbidden - Hanya pasien yang dapat mengakses resource ini'
            ], 403);
        }

        return $next($request);
    }
}
