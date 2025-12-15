<?php

// Test file untuk messaging API
// Jalankan via: php test_messaging.php

$API_URL = 'http://127.0.0.1:8000/api/v1';

// Example login credentials (pastikan sudah ada di database)
$emailPasien = 'pasien@example.com';
$emailDokter = 'dokter@example.com';
$password = 'Password123!';

echo "=== MESSAGING API TEST ===\n\n";

// 1. Login sebagai Pasien
echo "1. LOGIN PASIEN...\n";
$ch = curl_init("$API_URL/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $emailPasien,
    'password' => $password
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data']['token'])) {
    $tokenPasien = $data['data']['token'];
    $userIdPasien = $data['data']['user']['id'];
    echo "✓ Login berhasil. Token: " . substr($tokenPasien, 0, 20) . "...\n";
    echo "  User ID: $userIdPasien\n\n";
} else {
    echo "✗ Login gagal\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 2. Get dokter untuk di-chat
echo "2. CARI DOKTER...\n";
$ch = curl_init("$API_URL/dokter/search/advanced?specialization=Dokter%20Anak&page=1&per_page=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'][0]['id'])) {
    $dokterIds = array_slice(array_column($data['data'], 'id'), 0, 2);
    echo "✓ Dokter ditemukan: " . implode(', ', $dokterIds) . "\n\n";
} else {
    echo "✗ Dokter tidak ditemukan\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 3. Create conversation dengan dokter pertama
echo "3. CREATE CONVERSATION...\n";
$dokterIdTarget = $dokterIds[0];
$ch = curl_init("$API_URL/messages/conversations");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'with_user_id' => $dokterIdTarget
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data']['id'])) {
    $conversationId = $data['data']['id'];
    echo "✓ Conversation dibuat. ID: $conversationId\n\n";
} else {
    echo "✗ Conversation gagal dibuat\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 4. Send message
echo "4. SEND MESSAGE...\n";
$ch = curl_init("$API_URL/messages/conversations/$conversationId/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'content' => 'Halo dokter, saya ingin berkonsultasi tentang demam anak saya'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data']['id'])) {
    echo "✓ Message terkirim. ID: " . $data['data']['id'] . "\n";
    echo "  Content: " . $data['data']['content'] . "\n\n";
} else {
    echo "✗ Message gagal terkirim\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 5. Get conversations list
echo "5. GET CONVERSATIONS LIST...\n";
$ch = curl_init("$API_URL/messages/conversations?page=1&per_page=10");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Conversations berhasil diambil. Total: " . count($data['data']) . "\n";
    foreach ($data['data'] as $conv) {
        echo "  - Conversation ID " . $conv['id'] . ": " . $conv['last_message_preview'] . "\n";
    }
    echo "\n";
} else {
    echo "✗ Conversations gagal diambil\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
}

// 6. Get messages dalam conversation
echo "6. GET MESSAGES IN CONVERSATION...\n";
$ch = curl_init("$API_URL/messages/conversations/$conversationId/messages?page=1&per_page=10");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Messages berhasil diambil. Total: " . count($data['data']) . "\n";
    foreach ($data['data'] as $msg) {
        echo "  - [" . $msg['sender']['name'] . "]: " . $msg['content'] . "\n";
    }
    echo "\n";
} else {
    echo "✗ Messages gagal diambil\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
}

// 7. Get unread count
echo "7. GET UNREAD COUNT...\n";
$ch = curl_init("$API_URL/messages/unread-count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $tokenPasien
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Total unread: " . $data['data']['total_unread'] . "\n\n";
} else {
    echo "✗ Unread count gagal diambil\n\n";
}

echo "=== TEST SELESAI ===\n";
