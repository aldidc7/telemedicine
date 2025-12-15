<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Controller;

class BaseApiController extends Controller
{
    /**
     * ===============================================
     * SWAGGER DOCUMENTATION BASE
     * ===============================================
     * 
     * Dokumentasi OpenAPI 3.0 untuk semua API endpoints
     * 
     * @OA\Info(
     *      title="Telemedicine API",
     *      version="1.0.0",
     *      description="REST API untuk Aplikasi Telemedicine RSUD dr. R. Soedarsono",
     *      @OA\Contact(
     *          email="support@telemedicine.local"
     *      ),
     *      @OA\License(
     *          name="Proprietary",
     *          url="https://telemedicine.local"
     *      )
     * )
     * 
     * @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="API Server"
     * )
     * 
     * @OA\SecurityScheme(
     *      type="http",
     *      description="Sanctum Token Authentication",
     *      name="Authorization",
     *      in="header",
     *      scheme="bearer",
     *      bearerFormat="JWT",
     *      securityScheme="bearerAuth",
     * )
     * 
     * @OA\Schema(
     *      schema="User",
     *      type="object",
     *      title="User Model",
     *      required={"id", "name", "email"},
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="name", type="string"),
     *      @OA\Property(property="email", type="string", format="email"),
     *      @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *      @OA\Property(property="created_at", type="string", format="date-time"),
     *      @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     * 
     * @OA\Schema(
     *      schema="Konsultasi",
     *      type="object",
     *      title="Konsultasi Model",
     *      required={"id", "pasien_id", "dokter_id", "status"},
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="pasien_id", type="integer"),
     *      @OA\Property(property="dokter_id", type="integer"),
     *      @OA\Property(property="keluhan", type="string"),
     *      @OA\Property(property="status", type="string", enum={"aktif", "selesai", "dibatalkan"}),
     *      @OA\Property(property="created_at", type="string", format="date-time"),
     *      @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     * 
     * @OA\Schema(
     *      schema="PesanChat",
     *      type="object",
     *      title="PesanChat Model",
     *      required={"id", "konsultasi_id", "pengirim_id", "pesan"},
     *      @OA\Property(property="id", type="integer"),
     *      @OA\Property(property="konsultasi_id", type="integer"),
     *      @OA\Property(property="pengirim_id", type="integer"),
     *      @OA\Property(property="pesan", type="string"),
     *      @OA\Property(property="tipe_pesan", type="string", enum={"text", "image", "file", "audio"}),
     *      @OA\Property(property="url_file", type="string", format="uri", nullable=true),
     *      @OA\Property(property="dibaca", type="boolean"),
     *      @OA\Property(property="dibaca_at", type="string", format="date-time", nullable=true),
     *      @OA\Property(property="created_at", type="string", format="date-time"),
     * )
     * 
     * @OA\Schema(
     *      schema="ApiResponse",
     *      type="object",
     *      title="API Response",
     *      @OA\Property(property="success", type="boolean"),
     *      @OA\Property(property="message", type="string"),
     *      @OA\Property(property="data", type="object"),
     * )
     * 
     * @OA\Schema(
     *      schema="ErrorResponse",
     *      type="object",
     *      title="Error Response",
     *      @OA\Property(property="success", type="boolean", default=false),
     *      @OA\Property(property="message", type="string"),
     *      @OA\Property(property="errors", type="object"),
     * )
     * 
     * @OA\Tag(
     *      name="Authentication",
     *      description="User authentication endpoints"
     * )
     * 
     * @OA\Tag(
     *      name="Pasien",
     *      description="Patient management endpoints"
     * )
     * 
     * @OA\Tag(
     *      name="Dokter",
     *      description="Doctor management endpoints"
     * )
     * 
     * @OA\Tag(
     *      name="Konsultasi",
     *      description="Consultation management endpoints"
     * )
     * 
     * @OA\Tag(
     *      name="Chat",
     *      description="Chat message endpoints (Real-time via WebSocket)"
     * )
     * 
     * @OA\Tag(
     *      name="Rating",
     *      description="Consultation rating endpoints"
     * )
     * 
     * @OA\Tag(
     *      name="Admin",
     *      description="Admin dashboard endpoints"
     * )
     */
    protected function apiResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function apiError($message, $errors = null, $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
