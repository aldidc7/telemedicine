<?php

namespace App\OpenAPI;

/**
 * ========================================================
 * OpenAPI 3.0 Documentation untuk Telemedicine API
 * ========================================================
 * 
 * File ini generate OpenAPI spec dengan response examples
 * lengkap untuk semua error cases (400, 401, 403, 422, 500)
 * 
 * Endpoint: GET /api/docs/openapi.json
 * Swagger UI: GET /api/docs
 * 
 * @author Telemedicine Development Team
 * @version 1.1.0
 */
class ApiDocumentationEnhanced
{
    public static function getOpenApiSpec(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Telemedicine API',
                'version' => '1.1.0',
                'description' => 'API Documentation untuk Aplikasi Telemedicine dengan Error Response Examples',
                'contact' => [
                    'name' => 'Telemedicine Support',
                    'email' => 'support@telemedicine.local',
                    'url' => 'https://telemedicine.local/support',
                ],
                'license' => [
                    'name' => 'Proprietary',
                    'url' => 'https://telemedicine.local/license',
                ],
                'x-api-version' => '1.1.0',
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://localhost:8000') . '/api/v1',
                    'description' => 'Production Server',
                    'variables' => [
                        'protocol' => [
                            'enum' => ['http', 'https'],
                            'default' => 'https',
                        ],
                    ],
                ],
                [
                    'url' => 'http://localhost:8000/api/v1',
                    'description' => 'Development Server',
                ],
            ],
            'paths' => self::getPaths(),
            'components' => self::getComponents(),
            'tags' => self::getTags(),
            'security' => [
                ['bearerAuth' => []],
            ],
        ];
    }

    private static function getPaths(): array
    {
        return [
            // ============================================
            // AUTHENTICATION ENDPOINTS
            // ============================================
            '/auth/register' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'operationId' => 'registerUser',
                    'summary' => 'Register user baru',
                    'description' => 'Registrasi user baru sebagai pasien atau dokter dengan validasi lengkap',
                    'requestBody' => [
                        'required' => true,
                        'description' => 'User registration data',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/RegisterRequest',
                                ],
                                'example' => [
                                    'email' => 'user@example.com',
                                    'password' => 'SecurePass123!',
                                    'password_confirmation' => 'SecurePass123!',
                                    'name' => 'John Doe',
                                    'role' => 'pasien',
                                    'phone' => '08123456789',
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'User berhasil didaftarkan',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'email' => ['type' => 'string', 'format' => 'email'],
                                                    'name' => ['type' => 'string'],
                                                    'role' => ['type' => 'string', 'enum' => ['pasien', 'dokter', 'admin']],
                                                    'phone' => ['type' => 'string'],
                                                    'token' => ['type' => 'string', 'description' => 'JWT Token'],
                                                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'message' => 'User berhasil terdaftar',
                                        'data' => [
                                            'id' => 1,
                                            'email' => 'user@example.com',
                                            'name' => 'John Doe',
                                            'role' => 'pasien',
                                            'phone' => '08123456789',
                                            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
                                            'created_at' => '2024-01-15T10:30:00Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Bad Request - Request format tidak valid',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'BAD_REQUEST',
                                            'message' => 'Request format tidak valid',
                                            'details' => [
                                                'info' => 'Invalid JSON format atau missing required fields',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '422' => [
                            'description' => 'Validation Error - Data tidak valid',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'VALIDATION_ERROR',
                                            'message' => 'Validation failed',
                                            'details' => [
                                                'validation_errors' => [
                                                    'email' => ['Email sudah terdaftar'],
                                                    'password' => ['Password minimal 8 karakter'],
                                                    'password_confirmation' => ['Password tidak sesuai'],
                                                    'name' => ['Nama wajib diisi'],
                                                    'role' => ['Role hanya boleh pasien atau dokter'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '429' => [
                            'description' => 'Too Many Requests - Rate limit exceeded',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/RateLimitError'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'TOO_MANY_REQUESTS',
                                            'message' => 'Terlalu banyak upaya registrasi. Silakan coba lagi nanti.',
                                            'details' => [
                                                'retry_after' => 900,
                                                'info' => 'Rate limit 3 registrasi per email per 15 menit',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => [
                            'description' => 'Internal Server Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'INTERNAL_SERVER_ERROR',
                                            'message' => 'Terjadi kesalahan pada server',
                                            'details' => [
                                                'request_id' => 'req_12345678',
                                                'info' => 'Silakan hubungi support dengan request_id',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'operationId' => 'loginUser',
                    'summary' => 'Login user',
                    'description' => 'Authenticate user dengan email dan password, return JWT token',
                    'requestBody' => [
                        'required' => true,
                        'description' => 'User login credentials',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => [
                                            'type' => 'string',
                                            'format' => 'email',
                                            'description' => 'Email address',
                                        ],
                                        'password' => [
                                            'type' => 'string',
                                            'format' => 'password',
                                            'description' => 'Password (minimum 8 characters)',
                                        ],
                                    ],
                                ],
                                'example' => [
                                    'email' => 'user@example.com',
                                    'password' => 'SecurePass123!',
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'token' => ['type' => 'string', 'description' => 'JWT Token'],
                                                    'token_type' => ['type' => 'string', 'default' => 'Bearer'],
                                                    'expires_in' => ['type' => 'integer', 'description' => 'Token expiration in seconds'],
                                                    'user' => ['$ref' => '#/components/schemas/User'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'message' => 'Login berhasil',
                                        'data' => [
                                            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
                                            'token_type' => 'Bearer',
                                            'expires_in' => 86400,
                                            'user' => [
                                                'id' => 1,
                                                'email' => 'user@example.com',
                                                'name' => 'John Doe',
                                                'role' => 'pasien',
                                                'phone' => '08123456789',
                                                'avatar_url' => 'https://...',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Bad Request',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'BAD_REQUEST',
                                            'message' => 'Email dan password harus diisi',
                                            'details' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Unauthorized - Invalid credentials',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'UNAUTHORIZED',
                                            'message' => 'Email atau password salah',
                                            'details' => [
                                                'info' => 'Pastikan email dan password benar',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '422' => [
                            'description' => 'Validation Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'VALIDATION_ERROR',
                                            'message' => 'Validation failed',
                                            'details' => [
                                                'validation_errors' => [
                                                    'email' => ['Format email tidak valid'],
                                                    'password' => ['Password harus diisi'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '429' => [
                            'description' => 'Too Many Requests - Rate limit exceeded',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/RateLimitError'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'TOO_MANY_REQUESTS',
                                            'message' => 'Terlalu banyak upaya login. Silakan coba lagi dalam 15 menit.',
                                            'details' => [
                                                'retry_after' => 900,
                                                'remaining' => 0,
                                                'info' => 'Rate limit 5 percobaan per IP per 15 menit',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => [
                            'description' => 'Internal Server Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'INTERNAL_SERVER_ERROR',
                                            'message' => 'Terjadi kesalahan pada server',
                                            'details' => [
                                                'request_id' => 'req_87654321',
                                                'timestamp' => '2024-01-15T10:30:00Z',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            '/auth/me' => [
                'get' => [
                    'tags' => ['Authentication'],
                    'operationId' => 'getCurrentUser',
                    'summary' => 'Get current authenticated user',
                    'description' => 'Ambil data user yang sedang login',
                    'security' => [
                        ['bearerAuth' => []],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Current user data',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/User'],
                                        ],
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'message' => 'Data user berhasil diambil',
                                        'data' => [
                                            'id' => 1,
                                            'email' => 'user@example.com',
                                            'name' => 'John Doe',
                                            'role' => 'pasien',
                                            'phone' => '08123456789',
                                            'avatar_url' => 'https://...',
                                            'created_at' => '2024-01-15T10:30:00Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Unauthorized - Token missing or invalid',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'UNAUTHORIZED',
                                            'message' => 'Token tidak valid atau expired',
                                            'details' => [
                                                'info' => 'Silakan login kembali',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '403' => [
                            'description' => 'Forbidden - Insufficient permissions',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'FORBIDDEN',
                                            'message' => 'Anda tidak memiliki akses ke resource ini',
                                            'details' => [
                                                'info' => 'Role anda tidak memiliki permission yang diperlukan',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => [
                            'description' => 'Internal Server Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'INTERNAL_SERVER_ERROR',
                                            'message' => 'Terjadi kesalahan pada server',
                                            'details' => [
                                                'request_id' => 'req_11111111',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            '/auth/logout' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'operationId' => 'logoutUser',
                    'summary' => 'Logout user',
                    'description' => 'Logout dan invalidate token JWT',
                    'security' => [
                        ['bearerAuth' => []],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Logout successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                        ],
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'message' => 'Logout berhasil',
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Unauthorized',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'UNAUTHORIZED',
                                            'message' => 'Token tidak valid',
                                            'details' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => [
                            'description' => 'Internal Server Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'INTERNAL_SERVER_ERROR',
                                            'message' => 'Terjadi kesalahan pada server',
                                            'details' => [
                                                'request_id' => 'req_22222222',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            '/auth/refresh' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'operationId' => 'refreshToken',
                    'summary' => 'Refresh JWT token',
                    'description' => 'Generate token JWT baru sebelum token lama expired',
                    'security' => [
                        ['bearerAuth' => []],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Token refreshed successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'token' => ['type' => 'string'],
                                                    'token_type' => ['type' => 'string'],
                                                    'expires_in' => ['type' => 'integer'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'message' => 'Token berhasil diperbarui',
                                        'data' => [
                                            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
                                            'token_type' => 'Bearer',
                                            'expires_in' => 86400,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Unauthorized - Token invalid or expired',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'UNAUTHORIZED',
                                            'message' => 'Token tidak valid atau sudah expired',
                                            'details' => [
                                                'info' => 'Silakan login ulang',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => [
                            'description' => 'Internal Server Error',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                    'example' => [
                                        'success' => false,
                                        'error' => [
                                            'code' => 'INTERNAL_SERVER_ERROR',
                                            'message' => 'Terjadi kesalahan pada server',
                                            'details' => [
                                                'request_id' => 'req_33333333',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function getComponents(): array
    {
        return [
            'schemas' => [
                // ============================================
                // REQUEST SCHEMAS
                // ============================================
                'RegisterRequest' => [
                    'type' => 'object',
                    'required' => ['email', 'password', 'password_confirmation', 'name', 'role'],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'Email yang unik',
                            'example' => 'user@example.com',
                        ],
                        'password' => [
                            'type' => 'string',
                            'format' => 'password',
                            'minLength' => 8,
                            'description' => 'Password minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka',
                            'example' => 'SecurePass123!',
                        ],
                        'password_confirmation' => [
                            'type' => 'string',
                            'format' => 'password',
                            'description' => 'Konfirmasi password harus sama dengan password',
                            'example' => 'SecurePass123!',
                        ],
                        'name' => [
                            'type' => 'string',
                            'description' => 'Nama lengkap user',
                            'example' => 'John Doe',
                        ],
                        'role' => [
                            'type' => 'string',
                            'enum' => ['pasien', 'dokter'],
                            'description' => 'Role user yang akan didaftarkan',
                            'example' => 'pasien',
                        ],
                        'phone' => [
                            'type' => 'string',
                            'description' => 'Nomor telepon (opsional)',
                            'example' => '08123456789',
                        ],
                    ],
                ],

                // ============================================
                // RESPONSE SCHEMAS
                // ============================================
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'description' => 'User ID',
                            'example' => 1,
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'User email',
                            'example' => 'user@example.com',
                        ],
                        'name' => [
                            'type' => 'string',
                            'description' => 'User name',
                            'example' => 'John Doe',
                        ],
                        'role' => [
                            'type' => 'string',
                            'enum' => ['pasien', 'dokter', 'admin'],
                            'description' => 'User role',
                            'example' => 'pasien',
                        ],
                        'phone' => [
                            'type' => 'string',
                            'description' => 'User phone number',
                            'example' => '08123456789',
                        ],
                        'avatar_url' => [
                            'type' => 'string',
                            'format' => 'uri',
                            'description' => 'Avatar URL',
                            'example' => 'https://example.com/avatars/user1.jpg',
                        ],
                        'created_at' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'description' => 'Created timestamp',
                            'example' => '2024-01-15T10:30:00Z',
                        ],
                        'updated_at' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'description' => 'Updated timestamp',
                            'example' => '2024-01-15T10:30:00Z',
                        ],
                    ],
                ],

                'ErrorResponse' => [
                    'type' => 'object',
                    'required' => ['success', 'error'],
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                            'default' => false,
                            'description' => 'Request success status',
                            'example' => false,
                        ],
                        'error' => [
                            'type' => 'object',
                            'required' => ['code', 'message'],
                            'properties' => [
                                'code' => [
                                    'type' => 'string',
                                    'description' => 'Error code (BAD_REQUEST, UNAUTHORIZED, FORBIDDEN, NOT_FOUND, INTERNAL_SERVER_ERROR, etc)',
                                    'example' => 'UNAUTHORIZED',
                                ],
                                'message' => [
                                    'type' => 'string',
                                    'description' => 'Human readable error message in Indonesian',
                                    'example' => 'Email atau password salah',
                                ],
                                'details' => [
                                    'type' => 'object',
                                    'description' => 'Additional error details',
                                    'example' => [
                                        'info' => 'Pastikan email dan password benar',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                'ValidationError' => [
                    'type' => 'object',
                    'required' => ['success', 'error'],
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                            'default' => false,
                            'example' => false,
                        ],
                        'error' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => [
                                    'type' => 'string',
                                    'default' => 'VALIDATION_ERROR',
                                    'description' => 'Validation error code',
                                    'example' => 'VALIDATION_ERROR',
                                ],
                                'message' => [
                                    'type' => 'string',
                                    'description' => 'Validation error message',
                                    'example' => 'Validation failed',
                                ],
                                'details' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'validation_errors' => [
                                            'type' => 'object',
                                            'description' => 'Field-level validation errors',
                                            'additionalProperties' => [
                                                'type' => 'array',
                                                'items' => ['type' => 'string'],
                                            ],
                                            'example' => [
                                                'email' => ['Email sudah terdaftar', 'Format email tidak valid'],
                                                'password' => ['Password minimal 8 karakter'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                'RateLimitError' => [
                    'type' => 'object',
                    'required' => ['success', 'error'],
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                            'default' => false,
                            'example' => false,
                        ],
                        'error' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => [
                                    'type' => 'string',
                                    'default' => 'TOO_MANY_REQUESTS',
                                    'description' => 'Rate limit exceeded',
                                    'example' => 'TOO_MANY_REQUESTS',
                                ],
                                'message' => [
                                    'type' => 'string',
                                    'description' => 'Rate limit error message',
                                    'example' => 'Terlalu banyak upaya login. Silakan coba lagi dalam 15 menit.',
                                ],
                                'details' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'retry_after' => [
                                            'type' => 'integer',
                                            'description' => 'Seconds to wait before retry',
                                            'example' => 900,
                                        ],
                                        'remaining' => [
                                            'type' => 'integer',
                                            'description' => 'Remaining attempts',
                                            'example' => 0,
                                        ],
                                        'info' => [
                                            'type' => 'string',
                                            'description' => 'Rate limit info',
                                            'example' => 'Rate limit 5 percobaan per IP per 15 menit',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                'Pagination' => [
                    'type' => 'object',
                    'properties' => [
                        'current_page' => ['type' => 'integer', 'example' => 1],
                        'per_page' => ['type' => 'integer', 'example' => 10],
                        'total' => ['type' => 'integer', 'example' => 100],
                        'total_pages' => ['type' => 'integer', 'example' => 10],
                        'from' => ['type' => 'integer', 'example' => 1],
                        'to' => ['type' => 'integer', 'example' => 10],
                    ],
                ],
            ],

            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'JWT Token dari login endpoint. Format: Authorization: Bearer <token>',
                ],
            ],
        ];
    }

    private static function getTags(): array
    {
        return [
            [
                'name' => 'Authentication',
                'description' => 'User authentication endpoints - Register, Login, Logout, Token Refresh',
                'externalDocs' => [
                    'description' => 'Learn more',
                    'url' => 'https://docs.telemedicine.local/auth',
                ],
            ],
            [
                'name' => 'Doctors',
                'description' => 'Doctor management and information endpoints',
            ],
            [
                'name' => 'Consultations',
                'description' => 'Consultation management endpoints',
            ],
            [
                'name' => 'Messages',
                'description' => 'Chat messages and real-time communication endpoints',
            ],
            [
                'name' => 'Appointments',
                'description' => 'Appointment scheduling and management',
            ],
            [
                'name' => 'Payments',
                'description' => 'Payment processing and transaction endpoints',
            ],
            [
                'name' => 'Ratings',
                'description' => 'Doctor ratings and reviews',
            ],
            [
                'name' => 'Medical Records',
                'description' => 'Patient medical data and health records',
            ],
        ];
    }
}
