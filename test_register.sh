php artisan tinker << 'EOF'
$client = new \GuzzleHttp\Client();

echo "=== TEST 1: REGISTRASI PASIEN ===\n";
$response = $client->post("http://localhost:8000/api/v1/auth/register", [
    'json' => [
        'name' => 'Ahmad Zaki',
        'email' => 'ahmad' . time() . '@test.com',
        'nik' => '1234567890123456',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pasien',
    ],
]);

echo "Status: " . $response->getStatusCode() . "\n";
$body = json_decode($response->getBody(), true);
echo json_encode($body['data']['user'] ?? $body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== TEST 2: REGISTRASI DOKTER ===\n";
$response = $client->post("http://localhost:8000/api/v1/auth/register", [
    'json' => [
        'name' => 'Dr. Budi Hartono',
        'email' => 'budi' . time() . '@test.com',
        'sip' => 'SIP-' . time(),
        'specialization' => 'Umum',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'dokter',
    ],
]);

echo "Status: " . $response->getStatusCode() . "\n";
$body = json_decode($response->getBody(), true);
echo json_encode($body['data']['user'] ?? $body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

exit();
EOF
