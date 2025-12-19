<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errorCode;

    public function __construct(
        string $message,
        int $statusCode = 400,
        string $errorCode = 'API_ERROR',
        Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => $this->message,
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode,
        ], $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
