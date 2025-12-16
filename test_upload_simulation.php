<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Setup request simulation with FormData-like multipart data
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Dokter;
use App\Models\User;

// Find an existing dokter or create test dokter
$dokter = Dokter::first() ?? Dokter::create([
    'user_id' => 1,
    'specialization' => 'Test',
    'license_number' => 'TEST123',
]);

echo "Testing photo upload for Dokter ID: " . $dokter->id . "\n";

// Create a real test image
$testImagePath = sys_get_temp_dir() . '/test_image_' . uniqid() . '.jpg';

// Create 1x1 pixel JPEG using GD
$image = imagecreatetruecolor(1, 1);
imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
imagejpeg($image, $testImagePath, 90);
imagedestroy($image);

echo "Test image created: $testImagePath\n";
echo "File size: " . filesize($testImagePath) . " bytes\n";

// Test DokterRequest validation
$uploadedFile = new UploadedFile(
    $testImagePath,
    'test_profile.jpg',
    'image/jpeg',
    null,
    true
);

echo "\nSimulating DokterRequest...\n";

// Simulate form data
$formData = [
    'name' => 'Dr Test Updated Again',
    'email' => 'test@example.com',
    'specialization' => 'Internal Medicine',
    'phone_number' => '081234567890',
    'profile_photo' => $uploadedFile,
];

// Create fake request
$request = new \Illuminate\Http\Request();
$request->initialize([], $formData, [], [], ['profile_photo' => $uploadedFile]);
$request->setMethod('PUT');
$request->setBaseUrl('http://localhost:8000');

// Test file validation
echo "\nFile validation:\n";
echo "  Has file: " . ($request->hasFile('profile_photo') ? 'Yes' : 'No') . "\n";
if ($request->hasFile('profile_photo')) {
    $file = $request->file('profile_photo');
    echo "  Filename: " . $file->getClientOriginalName() . "\n";
    echo "  MIME: " . $file->getMimeType() . "\n";
    echo "  Size: " . $file->getSize() . " bytes\n";
    echo "  Is valid: " . ($file->isValid() ? 'Yes' : 'No') . "\n";
}

// Test actual upload logic like DokterService
echo "\nTesting upload to storage...\n";
try {
    if ($request->hasFile('profile_photo')) {
        $file = $request->file('profile_photo');
        
        // Store file
        $path = $file->store('dokter-profiles', 'public');
        echo "  ✓ File stored successfully at: $path\n";
        
        // Verify file exists
        if (Storage::disk('public')->exists($path)) {
            echo "  ✓ File verified in storage\n";
        } else {
            echo "  ✗ File NOT found in storage\n";
        }
        
        // Now test updating dokter
        $dokter->profile_photo = '/storage/' . $path;
        $dokter->save();
        
        echo "  ✓ Dokter profile_photo updated: " . $dokter->profile_photo . "\n";
        
        // Verify in database
        $checkDokter = Dokter::find($dokter->id);
        if ($checkDokter->profile_photo === '/storage/' . $path) {
            echo "  ✓ Database update verified!\n";
        } else {
            echo "  ✗ Database value mismatch\n";
        }
    }
} catch (\Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    echo "  Trace: " . $e->getTraceAsString() . "\n";
}

// Cleanup
@unlink($testImagePath);
echo "\nTest complete!\n";
?>
