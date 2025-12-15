<?php

// Test registrasi pasien dan dokter
$baseUrl = 'http://localhost:8000/api/v1/auth';

// Test data
$testPasien = [
    'name' => 'Pasien Test',
    'email' => 'pasien' . time() . '@test.com',
    'nik' => '1234567890123456',
    'phone' => '081234567890',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'pasien',
];

$testDokter = [
    'name' => 'Dr. Dokter Test',
    'email' => 'dokter' . time() . '@test.com',
    'sip' => 'SIP-' . time(),
    'specialization' => 'Umum',
    'phone' => '081234567890',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'dokter',
];

function sendRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    return [
        'status' => $info['http_code'],
        'body' => $response,
    ];
}

echo "=== TEST REGISTRASI PASIEN ===\n";
$result = sendRequest($baseUrl . '/register', $testPasien);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . $result['body'] . "\n\n";

echo "=== TEST REGISTRASI DOKTER ===\n";
$result = sendRequest($baseUrl . '/register', $testDokter);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . $result['body'] . "\n";
