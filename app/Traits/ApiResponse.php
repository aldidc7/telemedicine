<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * API Response Trait
 * 
 * Provides consistent JSON response methods untuk controllers
 * Memastikan semua API responses memiliki format yang sama
 */
trait ApiResponse
{
    /**
     * Success response
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function successResponse($data = null, $message = 'Sukses', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'pesan' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param int $statusCode
     * @param array $data
     * @return JsonResponse
     */
    public function errorResponse($message = 'Terjadi kesalahan', $statusCode = 400, $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Created response (201)
     * 
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function createdResponse($data = null, $message = 'Resource berhasil dibuat'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Unauthorized response (401)
     * 
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorizedResponse($message = 'Tidak diizinkan'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response (403)
     * 
     * @param string $message
     * @return JsonResponse
     */
    public function forbiddenResponse($message = 'Akses ditolak'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Not found response (404)
     * 
     * @param string $message
     * @return JsonResponse
     */
    public function notFoundResponse($message = 'Resource tidak ditemukan'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Bad request response (400)
     * 
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    public function badRequestResponse($message = 'Permintaan tidak valid', $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => $message,
            'data' => $data,
        ], 400);
    }

    /**
     * Validation error response (422)
     * 
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public function validationErrorResponse($errors, $message = 'Validasi gagal'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'pesan' => $message,
            'data' => ['errors' => $errors],
        ], 422);
    }

    /**
     * Paginated response
     * 
     * @param $paginator
     * @param string $message
     * @return JsonResponse
     */
    public function paginatedResponse($paginator, $message = 'Sukses'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'pesan' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], 200);
    }
}
