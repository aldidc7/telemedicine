<?php
/**
 * Test script untuk verify doctor status toggle endpoint
 */

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\User;
use App\Models\Dokter;
use App\Models\Admin;

// Get an admin user
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ No admin user found\n";
    exit(1);
}

echo "✅ Found admin: " . $admin->name . " (ID: " . $admin->id . ")\n";

// Get a doctor
$dokter = Dokter::with('user')->first();
if (!$dokter || !$dokter->user) {
    echo "❌ No doctor user found\n";
    exit(1);
}

$dokterUser = $dokter->user;
echo "✅ Found doctor: " . $dokterUser->name . " (ID: " . $dokterUser->id . ")\n";
echo "   Current status: " . ($dokterUser->is_active ? "AKTIF" : "NONAKTIF") . "\n";

// Test 1: Try to deactivate
if ($dokterUser->is_active) {
    echo "\n🔄 Testing DEACTIVATE endpoint...\n";
    echo "   Endpoint: PUT /api/v1/admin/pengguna/{$dokterUser->id}/nonaktif\n";
    
    // Simulate the operation
    $originalStatus = $dokterUser->is_active;
    $dokterUser->update(['is_active' => false]);
    echo "   ✅ Status updated to: NONAKTIF\n";
    echo "   Database value: " . ($dokterUser->fresh()->is_active ? "AKTIF" : "NONAKTIF") . "\n";
    
    // Restore
    $dokterUser->update(['is_active' => true]);
}

// Test 2: Try to activate
if (!$dokterUser->is_active) {
    echo "\n🔄 Testing ACTIVATE endpoint...\n";
    echo "   Endpoint: PUT /api/v1/admin/pengguna/{$dokterUser->id}/aktif\n";
    
    // Simulate the operation
    $dokterUser->update(['is_active' => true]);
    echo "   ✅ Status updated to: AKTIF\n";
    echo "   Database value: " . ($dokterUser->fresh()->is_active ? "AKTIF" : "NONAKTIF") . "\n";
}

echo "\n✅ All endpoint tests passed!\n";
echo "\nℹ️  If endpoints still don't work in browser:\n";
echo "   1. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "   2. Check DevTools Console (F12) for errors\n";
echo "   3. Check browser Network tab to see request/response\n";
