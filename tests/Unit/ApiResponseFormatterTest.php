<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Responses\ApiResponseFormatter;

class ApiResponseFormatterTest extends TestCase
{
    /**
     * Test success response format
     */
    public function test_success_response_format()
    {
        $response = ApiResponseFormatter::success(
            data: ['id' => 1, 'name' => 'John'],
            message: 'Data retrieved successfully',
            statusCode: 200
        );

        $this->assertEquals(200, $response->status());
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('Data retrieved successfully', $data['message']);
        $this->assertEquals(['id' => 1, 'name' => 'John'], $data['data']);
    }

    /**
     * Test created response format
     */
    public function test_created_response_format()
    {
        $response = ApiResponseFormatter::created(
            data: ['id' => 1, 'name' => 'New Item'],
            message: 'Resource created'
        );

        $this->assertEquals(201, $response->status());
        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('Resource created', $data['message']);
    }

    /**
     * Test error response format
     */
    public function test_error_response_format()
    {
        $response = ApiResponseFormatter::error(
            message: 'Something went wrong',
            statusCode: 500,
            code: 'ERROR_500',
            details: ['field' => 'Invalid value']
        );

        $this->assertEquals(500, $response->status());
        $data = $response->getData(true);
        
        $this->assertFalse($data['success']);
        $this->assertEquals('Something went wrong', $data['error']['message']);
        $this->assertEquals(['field' => 'Invalid value'], $data['error']['details']);
    }

    /**
     * Test not found response
     */
    public function test_not_found_response()
    {
        $response = ApiResponseFormatter::notFound('Resource not found');

        $this->assertEquals(404, $response->status());
        $data = $response->getData(true);
        
        $this->assertFalse($data['success']);
    }

    /**
     * Test unauthorized response
     */
    public function test_unauthorized_response()
    {
        $response = ApiResponseFormatter::unauthorized('Unauthorized access');

        $this->assertEquals(401, $response->status());
        $data = $response->getData(true);
        
        $this->assertFalse($data['success']);
    }

    /**
     * Test paginated response format
     */
    public function test_paginated_response_format()
    {
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $response = ApiResponseFormatter::paginated(
            items: $items,
            pagination: [
                'current_page' => 1,
                'per_page' => 2,
                'total' => 10,
                'last_page' => 5,
                'from' => 1,
                'to' => 2,
            ],
            message: 'Items retrieved'
        );

        $data = $response->getData(true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals(1, $data['meta']['pagination']['current_page']);
        $this->assertEquals(2, $data['meta']['pagination']['per_page']);
        $this->assertEquals(10, $data['meta']['pagination']['total']);
    }
}
