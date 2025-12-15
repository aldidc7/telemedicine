<?php

require_once __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://localhost:8000/api/v1';

// Test credentials
$patientEmail = 'patient@test.com';
$patientPassword = 'password123';
$doctorEmail = 'dokter@test.com';
$doctorPassword = 'password123';

echo "========== APPOINTMENT SYSTEM TEST ==========\n\n";

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
echo "Patient logged in: ID=$patientId, Token={$patientToken}\n\n";

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
echo "Doctor logged in: ID=$doctorId, Token={$doctorToken}\n\n";

// Step 3: Get available slots
echo "[3] GET AVAILABLE SLOTS FOR DOCTOR\n";
$date = date('Y-m-d', strtotime('+1 day'));
$url = "$baseUrl/doctor/$doctorId/available-slots?date=$date";
$response = file_get_contents($url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$slots = json_decode($response, true);
echo "Available slots: " . count($slots['available_slots']) . "\n";
echo "Sample slots: " . json_encode(array_slice($slots['available_slots'], 0, 3)) . "\n\n";

// Step 4: Book appointment
echo "[4] BOOK APPOINTMENT (Patient)\n";
$scheduledAt = $slots['available_slots'][0];
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
            'reason' => 'Konsultasi kesehatan umum',
            'price' => 100000,
        ]),
    ],
]));
$appointment = json_decode($response, true);
if (!isset($appointment['data']['id'])) {
    echo "Booking failed!\n";
    print_r($appointment);
    exit;
}
$appointmentId = $appointment['data']['id'];
echo "Appointment booked successfully!\n";
echo "ID: $appointmentId\n";
echo "Status: {$appointment['data']['status']}\n";
echo "Scheduled: {$appointment['data']['scheduled_at']}\n\n";

// Step 5: Get appointment detail
echo "[5] GET APPOINTMENT DETAIL (Patient)\n";
$response = file_get_contents("$baseUrl/appointments/$appointmentId", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$detail = json_decode($response, true);
echo "Appointment Detail:\n";
echo "  ID: {$detail['data']['id']}\n";
echo "  Status: {$detail['data']['status']}\n";
echo "  Type: {$detail['data']['type']}\n";
echo "  Reason: {$detail['data']['reason']}\n";
echo "  Price: {$detail['data']['price']}\n\n";

// Step 6: Confirm appointment (Doctor)
echo "[6] CONFIRM APPOINTMENT (Doctor)\n";
$response = file_get_contents("$baseUrl/appointments/$appointmentId/confirm", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([]),
    ],
]));
$confirmed = json_decode($response, true);
echo "Status after confirm: {$confirmed['data']['status']}\n";
echo "Confirmed at: {$confirmed['data']['confirmed_at']}\n\n";

// Step 7: Get appointment statistics
echo "[7] GET APPOINTMENT STATS (Patient)\n";
$response = file_get_contents("$baseUrl/appointments/stats", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $patientToken",
    ],
]));
$stats = json_decode($response, true);
echo "Patient Statistics:\n";
echo "  Total: {$stats['data']['total']}\n";
echo "  Pending: {$stats['data']['pending']}\n";
echo "  Confirmed: {$stats['data']['confirmed']}\n";
echo "  Completed: {$stats['data']['completed']}\n";
echo "  Cancelled: {$stats['data']['cancelled']}\n";
echo "  Upcoming: {$stats['data']['upcoming']}\n\n";

// Step 8: Get doctor's appointments
echo "[8] LIST DOCTOR'S APPOINTMENTS\n";
$response = file_get_contents("$baseUrl/appointments", false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $doctorToken",
    ],
]));
$doctorAppointments = json_decode($response, true);
echo "Doctor has " . count($doctorAppointments['data']) . " appointments\n";
echo "Pagination: Page {$doctorAppointments['pagination']['current_page']} of {$doctorAppointments['pagination']['last_page']}\n\n";

// Step 9: Reschedule appointment (Patient)
echo "[9] RESCHEDULE APPOINTMENT (Patient)\n";
$newScheduledAt = date('Y-m-d H:i:s', strtotime($scheduledAt . ' +2 hours'));
$response = file_get_contents("$baseUrl/appointments/$appointmentId/reschedule", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([
            'scheduled_at' => $newScheduledAt,
        ]),
    ],
]));
$rescheduled = json_decode($response, true);
echo "Rescheduled to: {$rescheduled['data']['scheduled_at']}\n";
echo "Status: {$rescheduled['data']['status']}\n\n";

// Step 10: Start appointment (Doctor)
echo "[10] START APPOINTMENT (Doctor)\n";
// First confirm again since rescheduling resets to pending
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

$response = file_get_contents("$baseUrl/appointments/$appointmentId/start", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([]),
    ],
]));
$started = json_decode($response, true);
echo "Status: {$started['data']['status']}\n";
echo "Started at: {$started['data']['started_at']}\n\n";

// Step 11: End appointment (Doctor)
echo "[11] END APPOINTMENT (Doctor)\n";
$response = file_get_contents("$baseUrl/appointments/$appointmentId/end", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([
            'notes' => 'Konsultasi selesai, pasien diberikan resep dan saran istirahat',
        ]),
    ],
]));
$ended = json_decode($response, true);
echo "Status: {$ended['data']['status']}\n";
echo "Ended at: {$ended['data']['ended_at']}\n";
echo "Notes: {$ended['data']['notes']}\n\n";

// Step 12: Cancel appointment (create new one)
echo "[12] CANCEL APPOINTMENT (Patient)\n";
$response = file_get_contents("$baseUrl/appointments", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([
            'doctor_id' => $doctorId,
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'type' => 'video_call',
            'reason' => 'Follow-up consultation',
        ]),
    ],
]));
$cancelAppointment = json_decode($response, true);
$cancelAppointmentId = $cancelAppointment['data']['id'];

$response = file_get_contents("$baseUrl/appointments/$cancelAppointmentId/cancel", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([
            'reason' => 'Sudah merasa sehat, tidak perlu follow-up',
        ]),
    ],
]));
$cancelled = json_decode($response, true);
echo "Status: {$cancelled['data']['status']}\n";
echo "Cancelled at: {$cancelled['data']['cancelled_at']}\n";
echo "Reason: {$cancelled['data']['cancellation_reason']}\n\n";

// Step 13: Reject appointment (create new one)
echo "[13] REJECT APPOINTMENT (Doctor)\n";
$response = file_get_contents("$baseUrl/appointments", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $patientToken",
        ],
        'content' => json_encode([
            'doctor_id' => $doctorId,
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'type' => 'phone_call',
            'reason' => 'Konsultasi keluhan baru',
        ]),
    ],
]));
$rejectAppointment = json_decode($response, true);
$rejectAppointmentId = $rejectAppointment['data']['id'];

$response = file_get_contents("$baseUrl/appointments/$rejectAppointmentId/reject", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            "Authorization: Bearer $doctorToken",
        ],
        'content' => json_encode([
            'reason' => 'Waktu tidak tersedia, silakan pilih waktu lain',
        ]),
    ],
]));
$rejected = json_decode($response, true);
echo "Status: {$rejected['data']['status']}\n";
echo "Cancelled at: {$rejected['data']['cancelled_at']}\n";
echo "Reason: {$rejected['data']['cancellation_reason']}\n\n";

echo "========== TEST COMPLETED SUCCESSFULLY ==========\n";
