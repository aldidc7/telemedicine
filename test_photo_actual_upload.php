<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\UploadedFile;

// Setup Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Simulate Laravel request with FormData
// In real scenario, this is done by axios FormData

// Test case 1: Prepare a test image file
$testImagePath = __DIR__ . '/test_image.jpg';

// Create a simple test image (1x1 pixel JPEG)
$jpegData = base64_decode('
/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a
HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIy
MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIA
AhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8VAFQEB
AQAAAAAAAAAAAAAAAAAAAP/EABYRAQEBAAAAAAAAAAAAAAAAAAAEEf/aAAwDAQACEQMRAD8AmwCdAAAB
')
.
'//Z';

file_put_contents($testImagePath, $jpegData);

// Test with real file using Laravel's UploadedFile
echo "Testing image file exists: ";
var_dump(file_exists($testImagePath));

// Create a fake HTTP request with FormData
$fakeFormData = [
    'name' => 'Dr. Test Updated',
    'email' => 'test@example.com',
    'specialization' => 'Cardiology',
    'phone_number' => '081234567890',
    'profile_photo' => new UploadedFile(
        $testImagePath,
        'test_image.jpg',
        'image/jpeg',
        null,
        true
    )
];

echo "\n\nFormData simulation:\n";
foreach ($fakeFormData as $key => $value) {
    if ($value instanceof UploadedFile) {
        echo "$key: File - " . $value->getClientOriginalName() . " (" . $value->getSize() . " bytes)\n";
    } else {
        echo "$key: " . $value . "\n";
    }
}

// Test DokterRequest validation
$app->make('Illuminate\Foundation\Application')->make('router');
$request = app('request');

// Simulate FormRequest validation
echo "\n\nNow testing with Artisan command...\n";
?>
