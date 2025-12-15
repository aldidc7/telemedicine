<?php

// Test file untuk analytics API
// Jalankan via: php test_analytics.php

$API_URL = 'http://127.0.0.1:8000/api/v1';

// Example login credentials (admin/dokter role diperlukan)
$email = 'admin@example.com';
$password = 'Password123!';

echo "=== ANALYTICS API TEST ===\n\n";

// 1. Login
echo "1. LOGIN...\n";
$ch = curl_init("$API_URL/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $email,
    'password' => $password
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data']['token'])) {
    $token = $data['data']['token'];
    echo "✓ Login berhasil\n\n";
} else {
    echo "✗ Login gagal\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    exit;
}

// 2. Dashboard Overview
echo "2. GET DASHBOARD OVERVIEW...\n";
$ch = curl_init("$API_URL/analytics/overview");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Overview data:\n";
    foreach ($data['data'] as $key => $value) {
        echo "  - $key: $value\n";
    }
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

// 3. Consultation Metrics
echo "3. GET CONSULTATION METRICS...\n";
$ch = curl_init("$API_URL/analytics/consultations?period=month");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Consultation metrics retrieved\n";
    echo json_encode($data['data'], JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "✗ Failed\n\n";
}

// 4. Top-Rated Doctors
echo "4. GET TOP-RATED DOCTORS...\n";
$ch = curl_init("$API_URL/analytics/top-doctors?limit=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Top doctors: " . count($data['data']) . " items\n";
    foreach (array_slice($data['data'], 0, 3) as $doctor) {
        echo "  - {$doctor['name']}: {$doctor['average_rating']}⭐ ({$doctor['total_ratings']} ratings)\n";
    }
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

// 5. Patient Demographics
echo "5. GET PATIENT DEMOGRAPHICS...\n";
$ch = curl_init("$API_URL/analytics/patient-demographics");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Demographics:\n";
    echo "  - Total patients: " . $data['data']['total_patients'] . "\n";
    echo "  - Verified: " . $data['data']['verified_email'] . "\n";
    echo "  - Verification rate: " . $data['data']['verification_rate'] . "%\n";
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

// 6. Engagement Metrics
echo "6. GET ENGAGEMENT METRICS...\n";
$ch = curl_init("$API_URL/analytics/engagement?period=month");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Engagement metrics:\n";
    echo "  - Messages: " . $data['data']['messages_sent'] . "\n";
    echo "  - Completed consultations: " . $data['data']['consultations_completed'] . "\n";
    echo "  - Ratings given: " . $data['data']['ratings_given'] . "\n";
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

// 7. Growth Metrics
echo "7. GET GROWTH METRICS...\n";
$ch = curl_init("$API_URL/analytics/growth");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Growth metrics:\n";
    echo "  - Users today: " . $data['data']['users_today'] . "\n";
    echo "  - Users this week: " . $data['data']['users_this_week'] . "\n";
    echo "  - Consultations today: " . $data['data']['consultations_today'] . "\n";
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

// 8. User Retention
echo "8. GET USER RETENTION METRICS...\n";
$ch = curl_init("$API_URL/analytics/retention");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['data'])) {
    echo "✓ Retention metrics:\n";
    echo "  - New users (30d): " . $data['data']['new_users_30days'] . "\n";
    echo "  - Active users (30d): " . $data['data']['active_users_30days'] . "\n";
    echo "  - Retention rate: " . $data['data']['retention_rate_30days'] . "%\n";
    echo "\n";
} else {
    echo "✗ Failed\n\n";
}

echo "=== TEST SELESAI ===\n";
