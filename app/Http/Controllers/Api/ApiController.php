<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

/**
 * Base API Controller
 * 
 * Provides common functionality for all API controllers
 */
class ApiController extends Controller
{
    use ApiResponse;
}
