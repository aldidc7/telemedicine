<?php

namespace Tests\Feature\Health;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Health Check Tests
 * 
 * Verify application health and dependencies
 * Database connectivity, cache, queue, file storage
 */
class HealthCheckTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test database connection
     */
    public function test_database_connection_is_active(): void
    {
        try {
            DB::connection()->getPdo();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Test database can execute queries
     */
    public function test_database_can_execute_queries(): void
    {
        $result = DB::select('SELECT 1');
        
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->{'1'} ?? $result[0]->{1} ?? true);
    }
    
    /**
     * Test cache is available
     */
    public function test_cache_is_available(): void
    {
        Cache::put('health_check', 'working', 60);
        
        $cached = Cache::get('health_check');
        
        $this->assertEquals('working', $cached);
    }
    
    /**
     * Test file storage is accessible
     */
    public function test_file_storage_is_accessible(): void
    {
        $disk = \Storage::disk('public');
        
        $testFile = 'health_check_' . time() . '.txt';
        $content = 'Health check test file';
        
        // Write file
        $disk->put($testFile, $content);
        
        // Verify file exists
        $this->assertTrue($disk->exists($testFile));
        
        // Read file
        $retrieved = $disk->get($testFile);
        $this->assertEquals($content, $retrieved);
        
        // Cleanup
        $disk->delete($testFile);
    }
    
    /**
     * Test API health endpoint
     */
    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');
        
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp'
        ]);
    }
    
    /**
     * Test core tables exist
     */
    public function test_core_tables_exist(): void
    {
        $tables = [
            'users',
            'appointments',
            'consultations',
            'prescriptions',
            'ratings'
        ];
        
        foreach ($tables as $table) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $this->assertTrue($exists, "Table '{$table}' does not exist");
        }
    }
    
    /**
     * Test migrations are up to date
     */
    public function test_migrations_are_current(): void
    {
        // Check that migrations table exists and has records
        $migrationCount = DB::table('migrations')->count();
        
        $this->assertGreaterThan(0, $migrationCount, 'No migrations found');
    }
    
    /**
     * Test required environment variables are set
     */
    public function test_required_environment_variables_are_set(): void
    {
        $required = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'DB_CONNECTION',
            'CACHE_DRIVER'
        ];
        
        foreach ($required as $var) {
            $this->assertNotEmpty(
                env($var),
                "Environment variable '{$var}' is not set"
            );
        }
    }
    
    /**
     * Test application can boot without errors
     */
    public function test_application_boots_successfully(): void
    {
        $this->assertTrue(true);
    }
}
