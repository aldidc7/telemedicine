<?php

// Test file untuk notification API
// Jalankan via: php test_notifications.php

$API_URL = 'http://127.0.0.1:8000/api/v1';

// Example login credentials
$emailUser = 'pasien@example.com';
$password = 'Password123!';

echo "=== NOTIFICATION API TEST ===\n\n";

// 1. Login user
echo "1. LOGIN USER...\n";
$ch = curl_init("$API_URL/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $emailUser,
    'password' => $password
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data']['token'])) {
    $token = $data['data']['token'];
    $userId = $data['data']['user']['id'];
    echo "✓ Login berhasil. Token: " . substr($token, 0, 20) . "...\n";
    echo "  User ID: $userId\n\n";
} else {
    echo "✗ Login gagal\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 2. Get unread count
echo "2. GET UNREAD COUNT...\n";
$ch = curl_init("$API_URL/notifications/count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Unread count: " . $data['data']['unread_count'] . "\n\n";
} else {
    echo "✗ Failed to get unread count\n\n";
}

// 3. Get notifications list
echo "3. GET NOTIFICATIONS LIST...\n";
$ch = curl_init("$API_URL/notifications?page=1&per_page=10");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Notifications: " . count($data['data']) . " items\n";
    if (count($data['data']) > 0) {
        foreach ($data['data'] as $notif) {
            echo "  - [{$notif['type']}] {$notif['title']}: {$notif['message']}\n";
            echo "    Read: " . ($notif['read_at'] ? 'Yes' : 'No') . "\n";
        }
    }
    echo "\n";
} else {
    echo "✗ Failed to get notifications\n\n";
}

// 4. Get unread notifications
echo "4. GET UNREAD NOTIFICATIONS...\n";
$ch = curl_init("$API_URL/notifications/unread?limit=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Unread notifications: " . count($data['data']) . " items\n";
    if (count($data['data']) > 0) {
        foreach ($data['data'] as $notif) {
            echo "  - {$notif['title']}: {$notif['message']}\n";
        }
    }
    echo "\n";
} else {
    echo "✗ Failed to get unread notifications\n\n";
}

// 5. Get notification statistics
echo "5. GET NOTIFICATION STATISTICS...\n";
$ch = curl_init("$API_URL/notifications/stats");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Stats:\n";
    echo "  - Total: " . $data['data']['total'] . "\n";
    echo "  - Unread: " . $data['data']['unread'] . "\n";
    echo "  - By type: " . json_encode($data['data']['by_type']) . "\n\n";
} else {
    echo "✗ Failed to get stats\n\n";
}

// 6. Mark all as read
echo "6. MARK ALL AS READ...\n";
$ch = curl_init("$API_URL/notifications/read-all");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Marked " . $data['data']['count'] . " notifications as read\n\n";
} else {
    echo "✗ Failed to mark as read\n\n";
}

echo "=== TEST SELESAI ===\n";
