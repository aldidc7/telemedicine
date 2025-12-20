<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * ============================================
 * TRAIT: STANDARDIZED API RESPONSE
 * ============================================
 * 
 * Ensure semua API responses konsisten format
 * Easy to document, easier untuk client consume
 * 
 * Format:
 * {
 *   "success": true/false,
 *   "data": {...},
 *   "message": "...",
 *   "error_code": "...",
 *   "meta": {"pagination": {...}}
 * }
 */
trait StandardizedApiResponse
{
    /**
     * Success response dengan data
     */
    public function sendSuccess($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Success response dengan pagination
     */
    public function sendSuccessPaginated($data, $meta = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    public function sendError(
        string $message,
        $errors = null,
        int $statusCode = 400,
        string $errorCode = 'ERROR'
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public function sendValidationError($errors, string $message = 'Validation failed', int $statusCode = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'VALIDATION_ERROR',
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Unauthorized response
     */
    public function sendUnauthorized(string $message = 'Unauthorized', string $errorCode = 'UNAUTHORIZED'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], 401);
    }

    /**
     * Forbidden response
     */
    public function sendForbidden(string $message = 'Forbidden', string $errorCode = 'FORBIDDEN'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], 403);
    }

    /**
     * Not found response
     */
    public function sendNotFound(string $message = 'Not found', string $errorCode = 'NOT_FOUND'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], 404);
    }

    /**
     * Server error response
     */
    public function sendServerError(string $message = 'Server error', string $errorCode = 'SERVER_ERROR'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], 500);
    }

    /**
     * Created response (201)
     */
    public function sendCreated($data, string $message = 'Created successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    /**
     * No content response (204)
     */
    public function sendNoContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Format list response dengan meta
     */
    public function formatListResponse($items, int $total, int $page, int $perPage): array
    {
        return [
            'data' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }
}
