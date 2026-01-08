<?php

namespace App\Http\Controllers\Api;

class ApiController extends BaseApiController
{
    /**
     * Success response wrapper
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $message = 'Sukses', $statusCode = 200)
    {
        return $this->apiResponse($data, $message, $statusCode);
    }

    /**
     * Error response wrapper
     * 
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message, $errors = null, $statusCode = 400)
    {
        return $this->apiError($message, $errors, $statusCode);
    }
}
