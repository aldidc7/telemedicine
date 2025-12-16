<?php
// Simple test script to debug dashboard API

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Boot the application
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    // Test if we can access the models
    echo "=== Testing Models ===" . PHP_EOL;
    echo "Users count: " . \App\Models\User::count() . PHP_EOL;
    echo "Pasien count: " . \App\Models\Pasien::count() . PHP_EOL;
    echo "Dokter count: " . \App\Models\Dokter::count() . PHP_EOL;
    echo "Konsultasi count: " . \App\Models\Konsultasi::count() . PHP_EOL;
    echo "RekamMedis count: " . \App\Models\RekamMedis::count() . PHP_EOL;
    echo "ActivityLog count: " . \App\Models\ActivityLog::count() . PHP_EOL;
    echo "PesanChat count: " . \App\Models\PesanChat::count() . PHP_EOL;
    echo "Admin count: " . \App\Models\Admin::count() . PHP_EOL;

    echo "\n=== Testing Dashboard Method ===" . PHP_EOL;
    
    // Create a test request
    $user = \App\Models\User::where('role', 'admin')->first();
    if (!$user) {
        echo "ERROR: No admin user found!" . PHP_EOL;
        exit(1);
    }
    
    echo "Using admin user: " . $user->name . " (ID: " . $user->id . ")" . PHP_EOL;
    
    // Try to call the dashboard method directly
    $controller = new \App\Http\Controllers\Api\AdminController();
    
    // Mock auth
    \Auth::loginUsingId($user->id);
    
    // Call the method
    $response = $controller->dashboard();
    
    echo "Response status: " . $response->getStatusCode() . PHP_EOL;
    echo "Response: " . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . PHP_EOL;

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace: " . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
?>
