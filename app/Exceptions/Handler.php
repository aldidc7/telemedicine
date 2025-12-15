<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Global Exception Handler
 * 
 * Centralized error handling untuk semua exceptions
 * Mengembalikan JSON response yang consistent
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log exception
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    /**
     * Render an exception into a response.
     * 
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        // API request - return JSON
        if ($request->is('api/*')) {
            return $this->handleApiException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions dengan response format konsisten
     * 
     * @param Throwable $exception
     * @return JsonResponse
     */
    protected function handleApiException(Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);
        $data = [];

        // Include validation errors jika ada
        if (method_exists($exception, 'errors')) {
            $data['errors'] = $exception->errors();
        }

        // Include detailed error info di development
        if (config('app.debug')) {
            $data['debug'] = [
                'exception' => class_basename($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        return response()->json([
            'success' => false,
            'pesan' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Get HTTP status code dari exception
     * 
     * @param Throwable $exception
     * @return int
     */
    protected function getStatusCode(Throwable $exception): int
    {
        // Validation exception
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 422;
        }

        // Authentication exception
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return 401;
        }

        // Authorization exception
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 403;
        }

        // Not found exception
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 404;
        }

        // Method not allowed
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return 405;
        }

        // Conflict
        if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return 409;
        }

        // Get status code dari exception jika ada
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        // Default 500
        return 500;
    }

    /**
     * Get user-friendly error message
     * 
     * @param Throwable $exception
     * @return string
     */
    protected function getMessage(Throwable $exception): string
    {
        // Validation error
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 'Validation error';
        }

        // Authentication error
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return 'Unauthenticated';
        }

        // Authorization error
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 'Unauthorized';
        }

        // Not found
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 'Resource not found';
        }

        // Return exception message or generic error
        return $exception->getMessage() ?: 'Server error';
    }
}
