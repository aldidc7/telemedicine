<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * ============================================
 * CUSTOM LOGGER SERVICE
 * ============================================
 * 
 * Centralized logging untuk audit trail dan debugging
 */
class Logger
{
    const CHANNEL_API = 'api';
    const CHANNEL_AUTH = 'auth';
    const CHANNEL_TRANSACTION = 'transaction';
    const CHANNEL_ERROR = 'error';

    /**
     * Log API request
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $requestData
     * @param int|null $userId
     * @return void
     */
    public static function logApiRequest($method, $endpoint, $requestData = [], $userId = null)
    {
        /** @var User|null $user */
        $user = Auth::user();
        Log::channel(self::CHANNEL_API)->info('API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'user_id' => $userId ?? $user?->id,
            'ip_address' => request()->ip(),
            'request_data' => self::sanitizeData($requestData),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log API response
     * 
     * @param string $method
     * @param string $endpoint
     * @param int $statusCode
     * @param mixed $responseData
     * @param float $duration
     * @return void
     */
    public static function logApiResponse($method, $endpoint, $statusCode, $responseData = null, $duration = 0)
    {
        /** @var User|null $user */
        $user = Auth::user();
        Log::channel(self::CHANNEL_API)->info('API Response', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response_size' => strlen(json_encode($responseData)),
            'duration_ms' => $duration,
            'user_id' => $user?->id,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log authentication event
     * 
     * @param string $event (login, logout, failed_login, token_refresh)
     * @param int|null $userId
     * @param array $metadata
     * @return void
     */
    public static function logAuthEvent($event, $userId = null, $metadata = [])
    {
        /** @var User|null $user */
        $user = Auth::user();
        Log::channel(self::CHANNEL_AUTH)->info("Auth Event: {$event}", [
            'event' => $event,
            'user_id' => $userId ?? $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => $metadata,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log transaction
     * 
     * @param string $type (create, update, delete)
     * @param string $model
     * @param int $recordId
     * @param array $changes
     * @param int|null $userId
     * @return void
     */
    public static function logTransaction($type, $model, $recordId, $changes = [], $userId = null)
    {
        /** @var User|null $user */
        $user = Auth::user();
        Log::channel(self::CHANNEL_TRANSACTION)->info("Transaction: {$type} {$model}", [
            'type' => $type,
            'model' => $model,
            'record_id' => $recordId,
            'user_id' => $userId ?? $user?->id,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log error
     * 
     * @param \Throwable $exception
     * @param string $context
     * @param array $metadata
     * @return void
     */
    public static function logError(\Throwable $exception, $context = '', $metadata = [])
    {
        /** @var User|null $user */
        $user = Auth::user();
        Log::channel(self::CHANNEL_ERROR)->error('Application Error', [
            'exception' => class_basename($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'user_id' => $user?->id,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'metadata' => $metadata,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Sanitize sensitive data dari log
     * 
     * @param array $data
     * @return array
     */
    private static function sanitizeData($data)
    {
        $sensitive_keys = ['password', 'token', 'secret', 'api_key', 'credit_card', 'ssn'];
        
        foreach ($sensitive_keys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }
        
        return $data;
    }
}
