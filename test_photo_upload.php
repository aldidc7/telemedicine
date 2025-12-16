<?php

/**
 * Test script untuk debug photo upload - simplified
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dokter;
use Illuminate\Support\Facades\Storage;

echo "=== Photo Upload Test ===" . PHP_EOL;

// Test 1: Storage directories
echo "\n[1] Storage directories:" . PHP_EOL;
$dokterDir = storage_path('app/public/dokter-profiles');
echo "  Path: " . $dokterDir . PHP_EOL;
echo "  Exists: " . (is_dir($dokterDir) ? "✓" : "✗") . PHP_EOL;
echo "  Writable: " . (is_writable($dokterDir) ? "✓" : "✗") . PHP_EOL;

// Test 2: Dokter model
echo "\n[2] Dokter model:" . PHP_EOL;
$model = new Dokter();
$fillable = $model->getFillable();
echo "  profile_photo in fillable: " . (in_array('profile_photo', $fillable) ? "✓" : "✗") . PHP_EOL;

// Test 3: Database
echo "\n[3] Database check:" . PHP_EOL;
$dokter = Dokter::first();
if ($dokter) {
    echo "  Dokter found: ID " . $dokter->id . PHP_EOL;
    echo "  User: " . $dokter->user->name . PHP_EOL;
    echo "  profile_photo column: " . ($dokter->profile_photo ? "'{$dokter->profile_photo}'" : "NULL") . PHP_EOL;
} else {
    echo "  ✗ No dokter found" . PHP_EOL;
}

// Test 4: Filesystem write test
echo "\n[4] Filesystem write test:" . PHP_EOL;
try {
    $disk = Storage::disk('public');
    $testFile = 'test-upload-' . time() . '.txt';
    $disk->put($testFile, 'test content');
    echo "  Write test: ✓" . PHP_EOL;
    echo "  File: /storage/" . $testFile . PHP_EOL;
    $disk->delete($testFile);
} catch (\Exception $e) {
    echo "  Write test failed: " . $e->getMessage() . PHP_EOL;
}

// Test 5: DokterRequest
echo "\n[5] DokterRequest validation:" . PHP_EOL;
try {
    $request = new \App\Http\Requests\DokterRequest();
    $rules = $request->rules();
    if (isset($rules['profile_photo'])) {
        echo "  Rule defined: ✓" . PHP_EOL;
        echo "  Rule: " . json_encode($rules['profile_photo']) . PHP_EOL;
    } else {
        echo "  ✗ profile_photo rule not found" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . PHP_EOL;
}

echo "\n=== Test Complete ===" . PHP_EOL;

