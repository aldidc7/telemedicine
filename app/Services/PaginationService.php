<?php

namespace App\Services;

/**
 * Pagination Service
 * 
 * Standardize pagination across API
 * Prevent abuse by limiting maximum per_page parameter
 */
class PaginationService
{
    /**
     * Pagination limits
     */
    const LIMITS = [
        'min_per_page' => 1,
        'max_per_page' => 100,      // Prevent users from requesting 10,000 records at once
        'default_per_page' => 15,
    ];

    /**
     * Validate and sanitize pagination parameters
     *
     * @param int|null $perPage
     * @param int|null $page
     * @return array ['per_page' => int, 'page' => int]
     */
    public static function validate(?int $perPage = null, ?int $page = null): array
    {
        // Default values
        if (!$perPage) {
            $perPage = self::LIMITS['default_per_page'];
        }

        if (!$page) {
            $page = 1;
        }

        // Enforce limits
        $perPage = min($perPage, self::LIMITS['max_per_page']);
        $perPage = max($perPage, self::LIMITS['min_per_page']);

        // Ensure page is positive
        $page = max($page, 1);

        return [
            'per_page' => $perPage,
            'page' => $page,
        ];
    }

    /**
     * Create paginated response
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginated
     * @return array
     */
    public static function format($paginated): array
    {
        return [
            'data' => $paginated->items(),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'next' => $paginated->nextPageUrl(),
                'prev' => $paginated->previousPageUrl(),
            ],
        ];
    }

    /**
     * Create simple paginated response (for lightweight endpoints)
     *
     * @param array $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @return array
     */
    public static function formatSimple(array $items, int $total, int $perPage, int $currentPage = 1): array
    {
        $lastPage = ceil($total / $perPage);

        return [
            'data' => $items,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'from' => (($currentPage - 1) * $perPage) + 1,
                'to' => min($currentPage * $perPage, $total),
            ],
        ];
    }

    /**
     * Get pagination query parameters
     *
     * @return array ['per_page', 'page']
     */
    public static function getFromRequest(): array
    {
        return self::validate(
            request()->query('per_page'),
            request()->query('page')
        );
    }
}
