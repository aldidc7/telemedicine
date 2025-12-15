<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WebSocketService;
use Illuminate\Http\Request;

class BroadcastingController extends Controller
{
    /**
     * Authenticate WebSocket channel subscription
     * 
     * Handles authentication for private and presence channels
     * Required by Laravel Broadcasting
     */
    public function authenticate(Request $request)
    {
        // User must be authenticated
        if (!auth()->check()) {
            return abort(403, 'Unauthorized');
        }

        try {
            $webSocketService = new WebSocketService();
            return $webSocketService->authenticateChannel($request);
        } catch (\Exception $e) {
            \Log::error('Broadcasting authentication failed: ' . $e->getMessage());
            return abort(403, 'Authentication failed');
        }
    }

    /**
     * Get broadcasting configuration for frontend
     * 
     * Frontend clients use this to initialize Pusher
     */
    public function getConfig(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $webSocketService = new WebSocketService();
            $config = $webSocketService->getAuthenticationData(auth()->id());
            
            return response()->json([
                'status' => 'success',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get broadcasting config: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get configuration',
            ], 500);
        }
    }
}
