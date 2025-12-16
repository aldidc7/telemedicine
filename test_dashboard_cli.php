#!/usr/bin/env php
<?php

use App\Http\Controllers\Api\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// This ensures all service providers are registered
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    // Get an admin user
    $admin = User::where('role', 'admin')->first();
    
    if (!$admin) {
        echo json_encode([
            'error' => 'No admin user found',
            'users_count' => User::count(),
        ], JSON_PRETTY_PRINT);
        exit(1);
    }
    
    echo "Found admin user: {$admin->name} (ID: {$admin->id})\n";
    
    // Login
    Auth::loginUsingId($admin->id);
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    
    // Call the dashboard method
    $controller = new AdminController();
    $response = $controller->dashboard();
    
    // Get the JSON content
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    if ($response->getStatusCode() !== 200) {
        echo "ERROR: Status code {$response->getStatusCode()}\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
        exit(1);
    }
    
    echo "SUCCESS! Dashboard API returned:\n";
    echo json_encode(['success' => $data['success'], 'pesan' => $data['pesan']], JSON_PRETTY_PRINT) . "\n";
    exit(0);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile() . ':' . $e->getLine(),
        'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 5),
    ], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}
?>
