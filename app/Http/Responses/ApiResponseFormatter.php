<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API Response Handler
 * 
 * Ensures all API responses follow this consistent format:
 * 
 * Success Response:
 * {
 *   "success": true,
 *   "message": "Success message",
 *   "data": {...},
 *   "meta": {
 *     "pagination": {...}
 *   }
 * }
 * 
 * Error Response:
 * {
 *   "success": false,
 *   "error": {
 *     "code": "ERROR_CODE",
 *     "message": "Error message",
 *     "details": {...}
 *   }
 * }
 */
class ApiResponseFormatter
{
    /**
     * Success response dengan data
     */
    public static function success(
        $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Success response untuk resource yang baru dibuat (201)
     */
    public static function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Success response untuk resource yang diupdate
     */
    public static function updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * Success response untuk resource yang dihapus
     */
    public static function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return self::success(null, $message, 200);
    }

    /**
     * Paginated response
     * 
     * @param array $items
     * @param array $pagination Contains: total, per_page, current_page, last_page, from, to
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated($items, array $pagination, string $message = 'Success'): JsonResponse
    {
        return self::success(
            $items,
            $message,
            200,
            [
                'pagination' => [
                    'total' => $pagination['total'] ?? 0,
                    'per_page' => $pagination['per_page'] ?? 10,
                    'current_page' => $pagination['current_page'] ?? 1,
                    'last_page' => $pagination['last_page'] ?? 1,
                    'from' => $pagination['from'] ?? null,
                    'to' => $pagination['to'] ?? null,
                ],
            ]
        );
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'An error occurred',
        int $statusCode = 400,
        string $code = null,
        $details = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code ?? 'ERROR_' . $statusCode,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error (422)
     */
    public static function unprocessable(
        string $message = 'Validation failed',
        array $errors = [],
        string $code = 'VALIDATION_ERROR'
    ): JsonResponse {
        return self::error(
            $message,
            422,
            $code,
            ['validation_errors' => $errors]
        );
    }

    /**
     * Unauthorized error (401)
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        string $code = 'UNAUTHORIZED'
    ): JsonResponse {
        return self::error($message, 401, $code);
    }

    /**
     * Forbidden error (403)
     */
    public static function forbidden(
        string $message = 'Forbidden',
        string $code = 'FORBIDDEN'
    ): JsonResponse {
        return self::error($message, 403, $code);
    }

    /**
     * Not found error (404)
     */
    public static function notFound(
        string $message = 'Resource not found',
        string $code = 'NOT_FOUND'
    ): JsonResponse {
        return self::error($message, 404, $code);
    }

    /**
     * Conflict error (409)
     */
    public static function conflict(
        string $message = 'Conflict',
        string $code = 'CONFLICT'
    ): JsonResponse {
        return self::error($message, 409, $code);
    }

    /**
     * Too many requests error (429)
     */
    public static function tooManyRequests(
        string $message = 'Too many requests',
        string $code = 'RATE_LIMIT_EXCEEDED'
    ): JsonResponse {
        return self::error($message, 429, $code);
    }

    /**
     * Internal server error (500)
     */
    public static function internalError(
        string $message = 'Internal server error',
        string $code = 'INTERNAL_ERROR',
        $details = null
    ): JsonResponse {
        return self::error($message, 500, $code, $details);
    }

    /**
     * Service unavailable (503)
     */
    public static function serviceUnavailable(
        string $message = 'Service unavailable',
        string $code = 'SERVICE_UNAVAILABLE'
    ): JsonResponse {
        return self::error($message, 503, $code);
    }
}
