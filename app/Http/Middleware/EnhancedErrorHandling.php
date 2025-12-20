<?php

/**
 * @noinspection PhpUndefinedMethodInspection
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/** @noinspection PhpUndefinedMethodInspection */
/**
 * ============================================
 * MIDDLEWARE: ENHANCED ERROR HANDLING
 * ============================================
 * 
 * Handle semua errors dengan konsisten:
 * - Log errors untuk audit trail
 * - Standardized JSON responses
 * - Sensitive data masking
 * - Security-appropriate error messages
 */
class EnhancedErrorHandling
{
    /**
     * Handle the request
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Throwable $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Handle exception dengan logging dan response standardization
     */
    private function handleException(Request $request, \Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);
        $errorCode = $this->getErrorCode($exception);

        // Log error dengan context
        $this->logError($request, $exception, $statusCode);

        // Build response
        $response = [
            'success' => false,
            'error' => $message,
            'error_code' => $errorCode,
        ];

        // Add debug info hanya di development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => class_basename($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get HTTP status code dari exception
     */
    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof ValidationException) {
            return 422;
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return 404;
        }

        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof \PDOException) {
            return 500;
        }

        return 500;
    }

    /**
     * Get user-friendly error message
     */
    private function getMessage(\Throwable $exception): string
    {
        // Validation errors
        if ($exception instanceof ValidationException) {
            return 'Validation failed. Check your input.';
        }

        // Model not found
        if ($exception instanceof ModelNotFoundException) {
            return 'Resource not found.';
        }

        // Not found
        if ($exception instanceof NotFoundHttpException) {
            return 'Endpoint not found.';
        }

        // Database errors
        if ($exception instanceof \PDOException) {
            Log::error('Database error', ['exception' => $exception]);
            return 'Database error occurred. Please try again.';
        }

        // Generic production error
        if (!config('app.debug')) {
            return 'An error occurred. Please try again later.';
        }

        return $exception->getMessage();
    }

    /**
     * Get standardized error code untuk tracking
     */
    private function getErrorCode(\Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'VALIDATION_ERROR';
        }

        if ($exception instanceof ModelNotFoundException) {
            return 'MODEL_NOT_FOUND';
        }

        if ($exception instanceof NotFoundHttpException) {
            return 'NOT_FOUND';
        }

        if ($exception instanceof \PDOException) {
            return 'DATABASE_ERROR';
        }

        return 'INTERNAL_ERROR';
    }

    /**
     * Log error dengan full context
     */
    private function logError(Request $request, \Throwable $exception, int $statusCode): void
    {
        //noinspection PhpUndefinedMethodInspection
        $context = [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'user_id' => Auth::check() ? Auth::user()?->getAuthIdentifier() : null,
            'status_code' => $statusCode,
            'exception' => class_basename($exception),
            'message' => $exception->getMessage(),
        ];

        // Log level berdasarkan status code
        if ($statusCode >= 500) {
            Log::error('HTTP Error', $context);
        } elseif ($statusCode >= 400) {
            Log::warning('HTTP Warning', $context);
        }
    }
}
