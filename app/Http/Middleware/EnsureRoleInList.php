<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleInList
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized - Token diperlukan'
            ], 401);
        }

        $allowedRoles = explode(',', $roles);
        
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json([
                'message' => 'Forbidden - Anda tidak memiliki akses ke resource ini',
                'allowed_roles' => $allowedRoles,
                'user_role' => Auth::user()->role
            ], 403);
        }

        return $next($request);
    }
}
