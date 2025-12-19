<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/api/v1/admin/dashboard', 'GET', [], [], [], [
    'HTTP_AUTHORIZATION' => 'Bearer test_token',
    'HTTP_ACCEPT' => 'application/json'
]);

echo "Testing database tables...\n";

use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\Admin;

try {
    $pCount = Pasien::count();
    echo "Patients: $pCount\n";
} catch (\Exception $e) {
    echo "Patient error: " . $e->getMessage() . "\n";
}

try {
    $dCount = Dokter::count();
    echo "Doctors: $dCount\n";
} catch (\Exception $e) {
    echo "Doctor error: " . $e->getMessage() . "\n";
}

try {
    $kCount = Konsultasi::count();
    echo "Consultations: $kCount\n";
} catch (\Exception $e) {
    echo "Consultation error: " . $e->getMessage() . "\n";
}

try {
    $aCount = Admin::count();
    echo "Admins: $aCount\n";
} catch (\Exception $e) {
    echo "Admin error: " . $e->getMessage() . "\n";
}

// Test raw queries
use Illuminate\Support\Facades\DB;

echo "\nTesting raw queries...\n";
try {
    $count = DB::table('patients')->count();
    echo "patients table: $count\n";
} catch (\Exception $e) {
    echo "patients table error: " . $e->getMessage() . "\n";
}

try {
    $count = DB::table('doctors')->count();
    echo "doctors table: $count\n";
} catch (\Exception $e) {
    echo "doctors table error: " . $e->getMessage() . "\n";
}

try {
    $count = DB::table('consultations')->count();
    echo "consultations table: $count\n";
} catch (\Exception $e) {
    echo "consultations table error: " . $e->getMessage() . "\n";
}

try {
    $count = DB::table('admins')->count();
    echo "admins table: $count\n";
} catch (\Exception $e) {
    echo "admins table error: " . $e->getMessage() . "\n";
}
?>
