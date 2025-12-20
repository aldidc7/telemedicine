<?php

namespace App\Helpers;

use Illuminate\Pagination\Paginator;

/**
 * Pagination Helper untuk standardize pagination response format
 */
class PaginationHelper
{
    /**
     * Convert Paginator instance to array format for API response
     * 
     * @param Paginator $paginator
     * @return array
     */
    public static function toArray(Paginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'path' => $paginator->path(),
        ];
    }

    /**
     * Get paginated response
     * 
     * @param Paginator $paginator
     * @param string $message
     * @return array
     */
    public static function format(Paginator $paginator, string $message = 'Success'): array
    {
        return [
            'data' => $paginator->items(),
            'pagination' => self::toArray($paginator),
            'message' => $message,
        ];
    }

    /**
     * Get default pagination parameters for query string
     * 
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            'page' => request()->get('page', 1),
            'per_page' => min((int) request()->get('per_page', 10), 100), // Max 100 items per page
        ];
    }
}
