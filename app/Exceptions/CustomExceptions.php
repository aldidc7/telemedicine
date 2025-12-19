<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class InvalidCredentialsException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => 'Email atau password tidak valid.',
            'error_code' => 'INVALID_CREDENTIALS',
            'status_code' => 401,
        ], 401);
    }
}

class UnauthorizedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => 'Anda tidak memiliki akses ke resource ini.',
            'error_code' => 'UNAUTHORIZED',
            'status_code' => 403,
        ], 403);
    }
}

class ResourceNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => 'Resource tidak ditemukan.',
            'error_code' => 'NOT_FOUND',
            'status_code' => 404,
        ], 404);
    }
}

class ValidationFailedException extends Exception
{
    protected $errors;

    public function __construct(array $errors = [])
    {
        parent::__construct('Validasi gagal');
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => 'Validasi gagal',
            'error_code' => 'VALIDATION_FAILED',
            'errors' => $this->errors,
            'status_code' => 422,
        ], 422);
    }
}
