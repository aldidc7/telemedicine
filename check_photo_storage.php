<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dokter;

echo "=== Check Photo Storage ===" . PHP_EOL;

$dokter = Dokter::first();
if ($dokter) {
    echo "\nDokter ID: " . $dokter->id . PHP_EOL;
    echo "Name: " . $dokter->user->name . PHP_EOL;
    echo "Profile Photo in DB: " . ($dokter->profile_photo ?? 'NULL') . PHP_EOL;
    echo "Updated At: " . $dokter->updated_at . PHP_EOL;
    
    // Check if file exists
    if ($dokter->profile_photo) {
        $path = str_replace('/storage/', '', $dokter->profile_photo);
        $fullPath = storage_path('app/public/' . $path);
        echo "\nFile Path: " . $fullPath . PHP_EOL;
        echo "File Exists: " . (file_exists($fullPath) ? "✓ YES" : "✗ NO") . PHP_EOL;
        if (file_exists($fullPath)) {
            echo "File Size: " . filesize($fullPath) . " bytes" . PHP_EOL;
        }
    }
    
    // List dokter-profiles directory
    echo "\n\nDocuments in dokter-profiles directory:" . PHP_EOL;
    $dir = storage_path('app/public/dokter-profiles');
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "  - " . $file . " (" . filesize($dir . '/' . $file) . " bytes)" . PHP_EOL;
            }
        }
    } else {
        echo "  Directory does not exist!" . PHP_EOL;
    }
} else {
    echo "No dokter found" . PHP_EOL;
}
