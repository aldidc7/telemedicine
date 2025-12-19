<?php

namespace App\Services;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Query Monitoring Service
 * 
 * Track dan monitor database query performance
 * Helps identify slow queries dan optimization opportunities
 */
class QueryMonitoringService
{
    /**
     * Performance thresholds (in milliseconds)
     */
    const THRESHOLDS = [
        'critical' => 500,   // > 500ms = Critical
        'warning' => 100,    // > 100ms = Warning
        'info' => 50,        // > 50ms = Info (tracking only)
    ];

    /**
     * Query statistics collection
     */
    private static array $stats = [
        'total_queries' => 0,
        'total_time' => 0,
        'slow_queries' => [],
        'query_counts_by_table' => [],
        'query_counts_by_type' => [
            'SELECT' => 0,
            'INSERT' => 0,
            'UPDATE' => 0,
            'DELETE' => 0,
        ],
    ];

    /**
     * Register query listener
     * Call this in AppServiceProvider boot()
     *
     * @return void
     */
    public static function registerListener(): void
    {
        DB::listen(function (QueryExecuted $query) {
            self::logQuery($query);
        });
    }

    /**
     * Log individual query
     *
     * @param QueryExecuted $query
     * @return void
     */
    private static function logQuery(QueryExecuted $query): void
    {
        // Increment counters
        self::$stats['total_queries']++;
        self::$stats['total_time'] += $query->time;

        // Categorize by type
        $type = self::getQueryType($query->sql);
        if (isset(self::$stats['query_counts_by_type'][$type])) {
            self::$stats['query_counts_by_type'][$type]++;
        }

        // Track by table
        $table = self::extractTableName($query->sql);
        if ($table) {
            if (!isset(self::$stats['query_counts_by_table'][$table])) {
                self::$stats['query_counts_by_table'][$table] = 0;
            }
            self::$stats['query_counts_by_table'][$table]++;
        }

        // Log slow queries
        if ($query->time > self::THRESHOLDS['warning']) {
            self::recordSlowQuery($query);
        }
    }

    /**
     * Record slow query
     *
     * @param QueryExecuted $query
     * @return void
     */
    private static function recordSlowQuery(QueryExecuted $query): void
    {
        $level = $query->time > self::THRESHOLDS['critical'] ? 'critical' : 'warning';

        $slowQuery = [
            'time' => $query->time,
            'level' => $level,
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'timestamp' => now()->toDateTimeString(),
        ];

        self::$stats['slow_queries'][] = $slowQuery;

        // Log to appropriate level
        if ($level === 'critical') {
            Log::critical('Critical slow query detected: ' . $query->time . 'ms', $slowQuery);
        } else {
            Log::warning('Slow query detected: ' . $query->time . 'ms', $slowQuery);
        }
    }

    /**
     * Get query type (SELECT, INSERT, UPDATE, DELETE)
     *
     * @param string $sql
     * @return string
     */
    private static function getQueryType(string $sql): string
    {
        $sql = strtoupper(trim($sql));

        if (str_starts_with($sql, 'SELECT')) return 'SELECT';
        if (str_starts_with($sql, 'INSERT')) return 'INSERT';
        if (str_starts_with($sql, 'UPDATE')) return 'UPDATE';
        if (str_starts_with($sql, 'DELETE')) return 'DELETE';

        return 'OTHER';
    }

    /**
     * Extract table name from SQL
     *
     * @param string $sql
     * @return string|null
     */
    private static function extractTableName(string $sql): ?string
    {
        // Simple extraction - works for most cases
        if (preg_match('/from\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        if (preg_match('/into\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        if (preg_match('/update\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get current statistics
     *
     * @return array
     */
    public static function getStats(): array
    {
        $avgTime = self::$stats['total_queries'] > 0 
            ? round(self::$stats['total_time'] / self::$stats['total_queries'], 2) 
            : 0;

        return [
            'total_queries' => self::$stats['total_queries'],
            'total_time_ms' => round(self::$stats['total_time'], 2),
            'average_time_ms' => $avgTime,
            'slow_queries_count' => count(self::$stats['slow_queries']),
            'query_types' => self::$stats['query_counts_by_type'],
            'queries_by_table' => self::$stats['query_counts_by_table'],
            'slow_queries' => array_slice(self::$stats['slow_queries'], 0, 10), // Top 10 slow
        ];
    }

    /**
     * Reset statistics
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$stats = [
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries' => [],
            'query_counts_by_table' => [],
            'query_counts_by_type' => [
                'SELECT' => 0,
                'INSERT' => 0,
                'UPDATE' => 0,
                'DELETE' => 0,
            ],
        ];
    }

    /**
     * Check if query count is healthy
     *
     * @param int $expectedMax
     * @return bool
     */
    public static function isHealthy(int $expectedMax = 20): bool
    {
        return self::$stats['total_queries'] <= $expectedMax 
            && count(self::$stats['slow_queries']) <= 3;
    }

    /**
     * Get performance report
     *
     * @return string
     */
    public static function getReport(): string
    {
        $stats = self::getStats();

        $report = "\n╔════════════════════════════════════════════════════╗\n";
        $report .= "║         QUERY PERFORMANCE REPORT                   ║\n";
        $report .= "╚════════════════════════════════════════════════════╝\n\n";

        $report .= "📊 Query Statistics:\n";
        $report .= "  • Total Queries: {$stats['total_queries']}\n";
        $report .= "  • Total Time: {$stats['total_time_ms']}ms\n";
        $report .= "  • Average Time: {$stats['average_time_ms']}ms\n";
        $report .= "  • Slow Queries: {$stats['slow_queries_count']}\n\n";

        $report .= "📈 Query Types:\n";
        foreach ($stats['query_types'] as $type => $count) {
            $report .= "  • {$type}: {$count}\n";
        }
        $report .= "\n";

        if (!empty($stats['queries_by_table'])) {
            $report .= "🗄️  Queries by Table:\n";
            arsort($stats['queries_by_table']);
            foreach (array_slice($stats['queries_by_table'], 0, 5) as $table => $count) {
                $report .= "  • {$table}: {$count}\n";
            }
            $report .= "\n";
        }

        if (!empty($stats['slow_queries'])) {
            $report .= "⚠️  Top Slow Queries:\n";
            foreach (array_slice($stats['slow_queries'], 0, 3) as $index => $query) {
                $report .= "  " . ($index + 1) . ". {$query['time']}ms - " . substr($query['sql'], 0, 50) . "...\n";
            }
            $report .= "\n";
        }

        $health = self::isHealthy() ? "✅ Healthy" : "⚠️ Needs Optimization";
        $report .= "🎯 Status: {$health}\n";
        $report .= "════════════════════════════════════════════════════════\n";

        return $report;
    }
}
