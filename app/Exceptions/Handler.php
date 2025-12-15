<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            // Log exception dengan context detail
            \Log::error('Application Exception Occurred', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'url' => request()->url(),
                'method' => request()->method(),
                'ip' => request()->ip(),
            ]);

            // Report to Sentry if configured
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
        // API request - return JSON dengan format konsisten
        if ($request->is('api/*')) {
            return $this->handleApiException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions dengan response format konsisten menggunakan ApiResponse
     * 
     * @param Throwable $exception
     * @return JsonResponse
     */
    protected function handleApiException(Throwable $exception): JsonResponse
    {
        // Import ApiResponse
        $apiResponse = \App\Http\Responses\ApiResponse::class;

        // Validation exception
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $apiResponse::fromValidationException($exception);
        }

        // Authentication exception
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $apiResponse::unauthorized('Unauthenticated. Please login first.');
        }

        // Authorization exception
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $apiResponse::forbidden('You do not have permission to access this resource.');
        }

        // Not found exception
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return $apiResponse::notFound('The requested resource was not found.');
        }

        // Method not allowed
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return $apiResponse::error('Method not allowed', 405, null, 'METHOD_NOT_ALLOWED');
        }

        // Unique constraint violation
        if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return $apiResponse::conflict('This record already exists.');
        }

        // Throttle exception (from rate limiting)
        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return $apiResponse::tooManyRequests(
                'Too many requests. Please try again later.',
                $exception->getHeaders()['Retry-After'] ?? null
            );
        }

        // Generic exceptions
        if (config('app.debug')) {
            return $apiResponse::fromException($exception, true);
        }

        return $apiResponse::serverError('An error occurred. Please try again later.');
    }

}
