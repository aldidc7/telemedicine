<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponseFormatter;
use App\OpenAPI\ApiDocumentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiDocumentationController extends Controller
{
    /**
     * Get OpenAPI specification as JSON
     */
    public function openapi(): JsonResponse
    {
        return response()->json(ApiDocumentation::getOpenApiSpec());
    }

    /**
     * Get Swagger UI HTML
     */
    public function swagger(): Response
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Telemedicine API Documentation</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <redoc spec-url='/api/docs/openapi.json'></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@latest/bundles/redoc.standalone.js"></script>
</body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Get health check
     */
    public function health(): JsonResponse
    {
        return ApiResponseFormatter::success([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ], 'API is running');
    }
}
