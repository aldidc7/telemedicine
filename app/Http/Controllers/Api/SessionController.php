<?php

namespace App\Http\Controllers\Api;

use App\Models\UserSession;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * ============================================
 * KONTROLER SESSION MANAGEMENT - Issue #6
 * ============================================
 * 
 * Mengelola session user di berbagai device
 * 
 * Endpoint:
 * GET /api/v1/sessions - List all sessions
 * POST /api/v1/sessions/{id}/logout - Logout specific session
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class SessionController extends Controller
{
    use ApiResponse;

    /**
     * Get all active sessions for current user
     * 
     * GET /api/v1/sessions
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }

        $sessions = UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'token' => $session->token,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'device_name' => $session->device_name,
                    'browser' => $session->browser_info,
                    'os' => $session->os_info,
                    'last_activity_at' => $session->last_activity_at,
                    'created_at' => $session->created_at,
                    'expires_at' => $session->expires_at,
                ];
            });

        return $this->successResponse(
            $sessions,
            'Daftar session berhasil diambil'
        );
    }

    /**
     * Logout specific session (single device)
     * 
     * POST /api/v1/sessions/{id}/logout
     */
    public function logout(Request $request, $sessionId)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }

        $session = UserSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return $this->notFoundResponse('Session tidak ditemukan');
        }

        // Deactivate session
        $session->deactivate();

        return $this->successResponse(null, 'Session berhasil di-logout');
    }
}
