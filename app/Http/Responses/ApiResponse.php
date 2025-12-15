<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API Response Handler
 * 
 * Ensures all API responses follow consistent format:
 * {
 *   "success": true/false,
 *   "data": {...},     // For successful responses
 *   "error": {...},    // For error responses
 *   "meta": {...}      // Pagination, etc.
 * }
 */
class ApiResponse
{
    /**
     * Success response dengan data
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): JsonResponse
    {
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
     * Success response dengan created status
     */
    public static function created($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Success response dengan updated status
     */
    public static function updated($data = null, string $message = 'Updated successfully'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * Success response dengan deleted status
     */
    public static function deleted($data = null, string $message = 'Deleted successfully'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * Paginated response
     */
    public static function paginated($items, $pagination, string $message = 'Success'): JsonResponse
    {
        return self::success(
            $items,
            $message,
            200,
            [
                'pagination' => $pagination,
                'total' => $pagination['total'] ?? null,
                'page' => $pagination['current_page'] ?? null,
                'per_page' => $pagination['per_page'] ?? null,
                'last_page' => $pagination['last_page'] ?? null,
            ]
        );
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'Error',
        int $statusCode = 400,
        $errors = null,
        string $code = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code ?? $statusCode,
                'message' => $message,
            ],
        ];

        if ($errors !== null) {
            $response['error']['details'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error (422 Unprocessable Entity)
     */
    public static function unprocessable(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Unauthorized error (401)
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401, null, 'UNAUTHORIZED');
    }

    /**
     * Forbidden error (403)
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403, null, 'FORBIDDEN');
    }

    /**
     * Not found error (404)
     */
    public static function notFound(string $message = 'Not found'): JsonResponse
    {
        return self::error($message, 404, null, 'NOT_FOUND');
    }

    /**
     * Conflict error (409) - Resource already exists
     */
    public static function conflict(string $message = 'Conflict'): JsonResponse
    {
        return self::error($message, 409, null, 'CONFLICT');
    }

    /**
     * Too many requests (429) - Rate limit exceeded
     */
    public static function tooManyRequests(string $message = 'Too many requests', int $retryAfter = null): JsonResponse
    {
        $response = self::error($message, 429, null, 'RATE_LIMITED');

        if ($retryAfter) {
            $response->header('Retry-After', $retryAfter);
        }

        return $response;
    }

    /**
     * Server error (500)
     */
    public static function serverError(string $message = 'Internal server error', $errors = null): JsonResponse
    {
        return self::error($message, 500, $errors, 'SERVER_ERROR');
    }

    /**
     * Service unavailable (503)
     */
    public static function serviceUnavailable(string $message = 'Service temporarily unavailable'): JsonResponse
    {
        return self::error($message, 503, null, 'SERVICE_UNAVAILABLE');
    }

    /**
     * Build structured error from validation exception
     */
    public static function fromValidationException(\Illuminate\Validation\ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors();

        // Transform errors ke format yang lebih readable
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = $messages[0]; // Ambil pesan pertama per field
        }

        return self::unprocessable($formattedErrors, 'Validation failed');
    }

    /**
     * Build structured error dari exception umum
     */
    public static function fromException(\Exception $exception, bool $debug = false): JsonResponse
    {
        $message = $exception->getMessage() ?: 'An error occurred';
        $statusCode = 500;

        // Try to extract status code dari exception
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }

        $errors = null;
        if ($debug) {
            $errors = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return self::error($message, $statusCode, $errors);
    }
}
