<?php

namespace App\OpenAPI;

/**
 * OpenAPI 3.0 Documentation untuk Telemedicine API
 * 
 * File ini generate OpenAPI spec yang bisa digunakan dengan Swagger UI
 * Endpoint: GET /api/docs
 */
class ApiDocumentation
{
    public static function getOpenApiSpec(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Telemedicine API',
                'version' => '1.0.0',
                'description' => 'API Documentation untuk Aplikasi Telemedicine',
                'contact' => [
                    'name' => 'Telemedicine Support',
                    'email' => 'support@telemedicine.local',
                ],
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://localhost:8000') . '/api/v1',
                    'description' => 'API Server',
                ],
            ],
            'paths' => self::getPaths(),
            'components' => self::getComponents(),
            'tags' => self::getTags(),
        ];
    }

    private static function getPaths(): array
    {
        return [
            '/auth/register' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Register user baru',
                    'description' => 'Registrasi user baru sebagai pasien atau dokter',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/RegisterRequest',
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
                                                    'email' => ['type' => 'string'],
                                                    'name' => ['type' => 'string'],
                                                    'role' => ['type' => 'string', 'enum' => ['pasien', 'dokter', 'admin']],
                                                    'token' => ['type' => 'string'],
                                                ],
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
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Login user',
                    'description' => 'Authenticate user dengan email dan password',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => [
                                            'type' => 'string',
                                            'format' => 'email',
                                        ],
                                        'password' => [
                                            'type' => 'string',
                                            'format' => 'password',
                                        ],
                                    ],
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
                                                    'token' => ['type' => 'string'],
                                                    'user' => ['$ref' => '#/components/schemas/User'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Invalid credentials',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/dokter' => [
                'get' => [
                    'tags' => ['Doctors'],
                    'summary' => 'List dokter',
                    'description' => 'Get list dokter dengan pagination dan filter',
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'description' => 'Page number',
                            'schema' => ['type' => 'integer', 'default' => 1],
                        ],
                        [
                            'name' => 'per_page',
                            'in' => 'query',
                            'description' => 'Items per page',
                            'schema' => ['type' => 'integer', 'default' => 10],
                        ],
                        [
                            'name' => 'spesialisasi',
                            'in' => 'query',
                            'description' => 'Filter by specialization',
                            'schema' => ['type' => 'string'],
                        ],
                        [
                            'name' => 'search',
                            'in' => 'query',
                            'description' => 'Search by name or SKP',
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Doctors list',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => [
                                                'type' => 'array',
                                                'items' => ['$ref' => '#/components/schemas/Doctor'],
                                            ],
                                            'meta' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'pagination' => ['$ref' => '#/components/schemas/Pagination'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/konsultasi' => [
                'post' => [
                    'tags' => ['Consultations'],
                    'summary' => 'Create consultation',
                    'description' => 'Pasien membuat konsultasi baru',
                    'security' => [
                        ['bearerAuth' => []],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/CreateConsultationRequest',
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Consultation created',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'message' => ['type' => 'string'],
                                            'data' => ['$ref' => '#/components/schemas/Consultation'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['description' => 'Unauthorized'],
                        '422' => ['description' => 'Validation Error'],
                    ],
                ],
                'get' => [
                    'tags' => ['Consultations'],
                    'summary' => 'List consultations',
                    'security' => [
                        ['bearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'status',
                            'in' => 'query',
                            'schema' => [
                                'type' => 'string',
                                'enum' => ['pending', 'accepted', 'completed', 'rejected'],
                            ],
                        ],
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'default' => 1],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'List of consultations',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'data' => [
                                                'type' => 'array',
                                                'items' => ['$ref' => '#/components/schemas/Consultation'],
                                            ],
                                            'meta' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'pagination' => ['$ref' => '#/components/schemas/Pagination'],
                                                ],
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
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'email' => ['type' => 'string', 'format' => 'email'],
                        'name' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['pasien', 'dokter', 'admin']],
                        'phone' => ['type' => 'string'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Doctor' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'nama' => ['type' => 'string'],
                        'spesialisasi' => ['type' => 'string'],
                        'skp' => ['type' => 'string'],
                        'foto' => ['type' => 'string'],
                        'rating' => ['type' => 'number', 'format' => 'float'],
                        'total_rating' => ['type' => 'integer'],
                        'is_verified' => ['type' => 'boolean'],
                        'is_available' => ['type' => 'boolean'],
                        'consultation_fee' => ['type' => 'number', 'format' => 'float'],
                    ],
                ],
                'Consultation' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'pasien_id' => ['type' => 'integer'],
                        'dokter_id' => ['type' => 'integer'],
                        'keluhan' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['pending', 'accepted', 'completed', 'rejected']],
                        'tipe_konsultasi' => ['type' => 'string', 'enum' => ['text', 'video', 'audio']],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                        'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Pagination' => [
                    'type' => 'object',
                    'properties' => [
                        'total' => ['type' => 'integer'],
                        'per_page' => ['type' => 'integer'],
                        'current_page' => ['type' => 'integer'],
                        'last_page' => ['type' => 'integer'],
                        'from' => ['type' => 'integer'],
                        'to' => ['type' => 'integer'],
                    ],
                ],
                'ErrorResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'default' => false],
                        'error' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => ['type' => 'string'],
                                'message' => ['type' => 'string'],
                                'details' => ['type' => 'object'],
                            ],
                        ],
                    ],
                ],
                'ValidationError' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'default' => false],
                        'error' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => ['type' => 'string', 'default' => 'VALIDATION_ERROR'],
                                'message' => ['type' => 'string'],
                                'details' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'validation_errors' => ['type' => 'object'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'RegisterRequest' => [
                    'type' => 'object',
                    'required' => ['email', 'password', 'name', 'role'],
                    'properties' => [
                        'email' => ['type' => 'string', 'format' => 'email'],
                        'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                        'password_confirmation' => ['type' => 'string', 'format' => 'password'],
                        'name' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['pasien', 'dokter']],
                        'phone' => ['type' => 'string'],
                    ],
                ],
                'CreateConsultationRequest' => [
                    'type' => 'object',
                    'required' => ['dokter_id', 'keluhan', 'tipe_konsultasi'],
                    'properties' => [
                        'dokter_id' => ['type' => 'integer'],
                        'keluhan' => ['type' => 'string', 'minLength' => 10],
                        'tipe_konsultasi' => ['type' => 'string', 'enum' => ['text', 'video', 'audio']],
                        'riwayat_penyakit' => ['type' => 'string'],
                        'catatan_tambahan' => ['type' => 'string'],
                    ],
                ],
            ],
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Laravel Sanctum token',
                ],
            ],
        ];
    }

    private static function getTags(): array
    {
        return [
            [
                'name' => 'Authentication',
                'description' => 'User authentication endpoints',
            ],
            [
                'name' => 'Doctors',
                'description' => 'Doctor management endpoints',
            ],
            [
                'name' => 'Consultations',
                'description' => 'Consultation management endpoints',
            ],
            [
                'name' => 'Messages',
                'description' => 'Chat messages endpoints',
            ],
            [
                'name' => 'Ratings',
                'description' => 'Doctor ratings endpoints',
            ],
        ];
    }
}
