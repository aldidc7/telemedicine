<?php

require_once __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://localhost:8000/api/v1';

// Test credentials
$patientEmail = 'patient@test.com';
$patientPassword = 'password123';
$doctorEmail = 'dokter@test.com';
$doctorPassword = 'password123';

echo "========== PRESCRIPTION SYSTEM TEST ==========\n\n";

// Step 1: Login as patient
echo "[1] LOGIN PATIENT\n";
$response = file_get_contents("$baseUrl/auth/login", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'email' => $patientEmail,
            'password' => $patientPassword,
        ]),
    ],
]));
$patientData = json_decode($response, true);
if (!isset($patientData['data']['token'])) {
    echo "Patient login failed!\n";
    print_r($patientData);
    exit;
}
$patientToken = $patientData['data']['token'];
$patientId = $patientData['data']['user']['id'];
echo "Patient logged in: ID=$patientId\n\n";

// Step 2: Login as doctor
echo "[2] LOGIN DOCTOR\n";
$response = file_get_contents("$baseUrl/auth/login", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'email' => $doctorEmail,
            'password' => $doctorPassword,
        ]),
    ],
]));
$doctorData = json_decode($response, true);
if (!isset($doctorData['data']['token'])) {
    echo "Doctor login failed!\n";
    print_r($doctorData);
    exit;
}
$doctorToken = $doctorData['data']['token'];
$doctorId = $doctorData['data']['user']['id'];
echo "Doctor logged in: ID=$doctorId\n\n";

// Step 3: Book and complete an appointment first
echo "[3] CREATE & COMPLETE APPOINTMENT\n";
$date = date('Y-m-d', strtotime('+1 day'));
$url = "$baseUrl/doctor/$doctorId/available-slots?date=$date";
$response = file_get_contents($url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$slots = json_decode($response, true);
$scheduledAt = $slots['available_slots'][0];

// Book appointment
$response = file_get_contents("$baseUrl/appointments", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([
            'doctor_id' => $doctorId,
            'scheduled_at' => $scheduledAt,
            'type' => 'text_consultation',
            'reason' => 'Konsultasi kesehatan',
        ]),
    ],
]));
$appointmentData = json_decode($response, true);
$appointmentId = $appointmentData['data']['id'];
echo "Appointment booked: ID=$appointmentId\n";

// Confirm appointment
file_get_contents("$baseUrl/appointments/$appointmentId/confirm", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([]),
    ],
]));
echo "Appointment confirmed\n";

// Start appointment
file_get_contents("$baseUrl/appointments/$appointmentId/start", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([]),
    ],
]));
echo "Appointment started\n";

// End appointment
file_get_contents("$baseUrl/appointments/$appointmentId/end", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode(['notes' => 'Konsultasi selesai']),
    ],
]));
echo "Appointment completed\n\n";

// Step 4: Create prescription (doctor)
echo "[4] CREATE PRESCRIPTION (Doctor)\n";
$response = file_get_contents("$baseUrl/prescriptions", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([
            'appointment_id' => $appointmentId,
            'medications' => [
                [
                    'name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => '3x sehari',
                    'duration' => '5 hari',
                    'quantity' => 15,
                    'instructions' => 'Diminum setelah makan',
                ],
                [
                    'name' => 'Amoxicillin',
                    'dosage' => '500mg',
                    'frequency' => '2x sehari',
                    'duration' => '7 hari',
                    'quantity' => 14,
                    'instructions' => 'Diminum dengan air putih',
                ],
            ],
            'notes' => 'Resep untuk infeksi ringan',
            'instructions' => 'Minum obat secara teratur dan istirahat yang cukup',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]),
    ],
]));
$prescriptionData = json_decode($response, true);
if (!isset($prescriptionData['data']['id'])) {
    echo "Failed to create prescription!\n";
    print_r($prescriptionData);
    exit;
}
$prescriptionId = $prescriptionData['data']['id'];
echo "Prescription created: ID=$prescriptionId\n";
echo "Medications: " . count($prescriptionData['data']['medications']) . "\n";
echo "Status: {$prescriptionData['data']['status']}\n";
echo "Expires: {$prescriptionData['data']['expires_at']}\n\n";

// Step 5: Get prescription detail (patient)
echo "[5] GET PRESCRIPTION DETAIL (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/$prescriptionId", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$detail = json_decode($response, true);
echo "Prescription Detail:\n";
echo "  ID: {$detail['data']['id']}\n";
echo "  Doctor: {$detail['data']['doctor']['name']}\n";
echo "  Status: {$detail['data']['status']}\n";
echo "  Medications: " . count($detail['data']['medications']) . "\n";
echo "  Notes: {$detail['data']['notes']}\n";
echo "  Acknowledged: " . ($detail['data']['patient_acknowledged'] ? 'Yes' : 'No') . "\n\n";

// Step 6: Get patient's prescriptions
echo "[6] LIST PATIENT'S PRESCRIPTIONS\n";
$response = file_get_contents("$baseUrl/prescriptions", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$prescriptions = json_decode($response, true);
echo "Patient has " . count($prescriptions['data']) . " prescriptions\n";
foreach ($prescriptions['data'] as $rx) {
    echo "  - {$rx['id']}: {$rx['doctor']['name']} ({$rx['status']})\n";
}
echo "\n";

// Step 7: Get active prescriptions
echo "[7] GET ACTIVE PRESCRIPTIONS (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/active", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$activePrescriptions = json_decode($response, true);
echo "Active prescriptions: " . $activePrescriptions['count'] . "\n\n";

// Step 8: Get unacknowledged prescriptions
echo "[8] GET UNACKNOWLEDGED PRESCRIPTIONS (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/unacknowledged", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$unacknowledged = json_decode($response, true);
echo "Unacknowledged: " . $unacknowledged['count'] . "\n\n";

// Step 9: Acknowledge prescription (patient)
echo "[9] ACKNOWLEDGE PRESCRIPTION (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/$prescriptionId/acknowledge", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([]),
    ],
]));
$acknowledged = json_decode($response, true);
echo "Acknowledged: " . ($acknowledged['data']['patient_acknowledged'] ? 'Yes' : 'No') . "\n";
echo "Acknowledged at: {$acknowledged['data']['acknowledged_at']}\n\n";

// Step 10: Update prescription (doctor)
echo "[10] UPDATE PRESCRIPTION (Doctor)\n";
$response = file_get_contents("$baseUrl/prescriptions/$prescriptionId", false, stream_context_create([
    'http' => [
        'method' => 'PUT',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([
            'notes' => 'Resep diupdate: tambahkan vitamin C',
            'medications' => [
                [
                    'name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => '3x sehari',
                    'duration' => '5 hari',
                    'quantity' => 15,
                    'instructions' => 'Diminum setelah makan',
                ],
                [
                    'name' => 'Vitamin C',
                    'dosage' => '1000mg',
                    'frequency' => '1x sehari',
                    'duration' => '10 hari',
                    'quantity' => 10,
                    'instructions' => 'Diminum pagi',
                ],
            ],
        ]),
    ],
]));
$updated = json_decode($response, true);
echo "Updated notes: {$updated['data']['notes']}\n";
echo "Medications: " . count($updated['data']['medications']) . "\n\n";

// Step 11: Get prescription statistics
echo "[11] GET PRESCRIPTION STATS (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/stats", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$stats = json_decode($response, true);
echo "Statistics:\n";
echo "  Total: {$stats['data']['total']}\n";
echo "  Active: {$stats['data']['active']}\n";
echo "  Expired: {$stats['data']['expired']}\n";
echo "  Completed: {$stats['data']['completed']}\n";
echo "  Unacknowledged: {$stats['data']['unacknowledged']}\n";
echo "  Total Medications: {$stats['data']['total_medications']}\n\n";

// Step 12: Mark prescription as completed
echo "[12] MARK PRESCRIPTION AS COMPLETED (Patient)\n";
$response = file_get_contents("$baseUrl/prescriptions/$prescriptionId/complete", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([]),
    ],
]));
$completed = json_decode($response, true);
echo "Status: {$completed['data']['status']}\n\n";

// Step 13: Get appointment prescriptions
echo "[13] GET APPOINTMENT PRESCRIPTIONS\n";
$response = file_get_contents("$baseUrl/appointments/$appointmentId/prescriptions", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$appointmentPrescriptions = json_decode($response, true);
echo "Prescriptions for appointment: " . count($appointmentPrescriptions['data']) . "\n\n";

// Step 14: Check if appointment has prescription
echo "[14] CHECK IF APPOINTMENT HAS PRESCRIPTION\n";
$response = file_get_contents("$baseUrl/appointments/$appointmentId/has-prescription", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$hasRx = json_decode($response, true);
echo "Has prescription: " . ($hasRx['has_prescription'] ? 'Yes' : 'No') . "\n\n";

echo "========== TEST COMPLETED SUCCESSFULLY ==========\n";
