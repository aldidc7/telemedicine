# Concurrent Access Control - Testing Guide

**Date**: 2024
**Status**: Ready for Testing
**Focus**: Verification and Validation

## Quick Start

### 1. Setup Test Data

```bash
# Create test accounts
php artisan tinker

> $doctor = User::factory()->create(['role' => 'dokter', 'name' => 'Dr. Test']);
> $patient1 = User::factory()->create(['role' => 'pasien', 'name' => 'Patient 1']);
> $patient2 = User::factory()->create(['role' => 'pasien', 'name' => 'Patient 2']);

> echo "Doctor ID: {$doctor->id}";
> echo "Patient 1 ID: {$patient1->id}";
> echo "Patient 2 ID: {$patient2->id}";
```

### 2. Generate Auth Tokens

```bash
# Get tokens for testing
php artisan tinker

> $token1 = $patient1->createToken('test')->plainTextToken;
> $token2 = $patient2->createToken('test')->plainTextToken;
> $doctorToken = $doctor->createToken('test')->plainTextToken;

> echo "Patient 1 Token: {$token1}";
> echo "Patient 2 Token: {$token2}";
> echo "Doctor Token: {$doctorToken}";
```

## Test Scenarios

### Test 1: Double-Booking Prevention

**Objective**: Verify only one patient can book same appointment slot

**Setup**:
```json
{
  "doctor_id": 1,
  "scheduled_at": "2024-12-25 10:00:00",
  "type": "text_consultation",
  "reason": "Health checkup"
}
```

**Execution**:

#### Option A: Curl (Sequential)
```bash
# Patient 1 books
PATIENT1_TOKEN="token_here"
DOCTOR_ID=1
SCHEDULED_AT="2024-12-25 10:00:00"

curl -X POST http://localhost:8000/api/v1/appointments \
  -H "Authorization: Bearer $PATIENT1_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"doctor_id\": $DOCTOR_ID,
    \"scheduled_at\": \"$SCHEDULED_AT\",
    \"type\": \"text_consultation\",
    \"reason\": \"Health checkup\"
  }" \
  -w "\nStatus: %{http_code}\n"

# Patient 2 books same time
PATIENT2_TOKEN="token_here"

curl -X POST http://localhost:8000/api/v1/appointments \
  -H "Authorization: Bearer $PATIENT2_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"doctor_id\": $DOCTOR_ID,
    \"scheduled_at\": \"$SCHEDULED_AT\",
    \"type\": \"text_consultation\",
    \"reason\": \"Health checkup\"
  }" \
  -w "\nStatus: %{http_code}\n"
```

#### Option B: Parallel with GNU Parallel
```bash
#!/bin/bash

DOCTOR_ID=1
SCHEDULED_AT="2024-12-25 10:00:00"
TOKEN1="patient1_token"
TOKEN2="patient2_token"

book_appointment() {
  TOKEN=$1
  PATIENT=$2
  
  curl -s -X POST http://localhost:8000/api/v1/appointments \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "{
      \"doctor_id\": $DOCTOR_ID,
      \"scheduled_at\": \"$SCHEDULED_AT\",
      \"type\": \"text_consultation\",
      \"reason\": \"Health checkup - Patient $PATIENT\"
    }" | jq '.success, .message, .error'
}

export -f book_appointment
parallel book_appointment ::: "$TOKEN1" "$TOKEN2" ::: 1 2
```

#### Option C: Load Testing with Artillery

**File**: `artillery-booking.yml`
```yaml
config:
  target: "http://localhost:8000"
  phases:
    - duration: 5
      arrivalRate: 10
      name: "Booking attempts"

scenarios:
  - name: "Concurrent Booking"
    flow:
      - post:
          url: "/api/v1/appointments"
          headers:
            Authorization: "Bearer {{ $randomToken }}"
            Content-Type: "application/json"
          json:
            doctor_id: 1
            scheduled_at: "2024-12-25 10:00:00"
            type: "text_consultation"
            reason: "Test booking"
          capture:
            json: "$.message"
```

```bash
# Run load test
artillery run artillery-booking.yml
```

**Expected Results**:
```
✅ PASS: First request returns 201 Created
✅ PASS: Subsequent requests return 409 Conflict
✅ PASS: Error message: "Doctor sudah memiliki appointment pada waktu tersebut"
✅ PASS: Only 1 appointment created in database
✅ PASS: No deadlock errors in logs
```

**Verification**:
```bash
# Check appointments created
php artisan tinker

> Appointment::where('doctor_id', 1)
>   ->where('scheduled_at', '2024-12-25 10:00:00')
>   ->count();
# Expected: 1
```

---

### Test 2: Status Update Race Condition

**Objective**: Prevent invalid status transitions

**Setup**:
Create an appointment first, then test:
```bash
# Get existing appointment ID
APPOINTMENT_ID=123
```

**Execution**:

#### Test Case 1: Valid Confirmation (Doctor)
```bash
DOCTOR_TOKEN="doctor_token_here"
APPOINTMENT_ID=123

curl -X PUT http://localhost:8000/api/v1/appointments/$APPOINTMENT_ID \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed"}' \
  -w "\nStatus: %{http_code}\n"
```

**Expected**:
```json
{
  "success": true,
  "message": "Appointment confirmed",
  "data": {
    "id": 123,
    "status": "confirmed"
  }
}
```

#### Test Case 2: Invalid Transition (Patient cancels confirmed)
```bash
PATIENT_TOKEN="patient_token_here"

curl -X PUT http://localhost:8000/api/v1/appointments/$APPOINTMENT_ID \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "cancelled"}' \
  -w "\nStatus: %{http_code}\n"
```

**Expected**:
```json
{
  "success": false,
  "error": "Invalid status transition from confirmed to cancelled",
  "code": "INVALID_TRANSITION"
}
```

#### Test Case 3: Simultaneous Update Race
```bash
#!/bin/bash

# Terminal 1 & 2 - Run simultaneously
DOCTOR_TOKEN="doctor_token"
PATIENT_TOKEN="patient_token"
APPOINTMENT_ID=123

# Terminal 1: Doctor tries to confirm
curl -X PUT http://localhost:8000/api/v1/appointments/$APPOINTMENT_ID \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -d '{"status": "confirmed"}'

# Terminal 2: Patient tries to cancel simultaneously
curl -X PUT http://localhost:8000/api/v1/appointments/$APPOINTMENT_ID \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -d '{"status": "cancelled"}'

# One succeeds (201), one fails with 409 or 422
```

**Expected Results**:
```
✅ PASS: Only one status update succeeds
✅ PASS: No race condition in database
✅ PASS: Status transitions are validated
✅ PASS: No conflicting updates possible
```

**Verification**:
```bash
php artisan tinker

> $appointment = Appointment::find(123);
> echo $appointment->status;  # Should be one consistent state
```

---

### Test 3: Duplicate Prescription Prevention

**Objective**: Ensure only one prescription per completed appointment

**Setup**:
```bash
# Get a completed appointment
php artisan tinker

> $appointment = Appointment::where('status', 'completed')->first();
> echo $appointment->id;
```

**Execution**:

#### First Prescription (Should Succeed)
```bash
DOCTOR_TOKEN="doctor_token"
APPOINTMENT_ID=123

curl -X POST http://localhost:8000/api/v1/prescriptions \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "appointment_id": '$APPOINTMENT_ID',
    "medications": [
      {
        "name": "Paracetamol",
        "dosage": "500mg",
        "frequency": "3x sehari",
        "duration": "7 hari"
      }
    ],
    "instructions": "Minum setelah makan"
  }' \
  -w "\nStatus: %{http_code}\n"
```

**Expected**: `201 Created`

#### Duplicate Prescription (Should Fail)
```bash
# Run same request again immediately
curl -X POST http://localhost:8000/api/v1/prescriptions \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "appointment_id": '$APPOINTMENT_ID',
    "medications": [...]
  }' \
  -w "\nStatus: %{http_code}\n"
```

**Expected**: `409 Conflict`
```json
{
  "error": "Prescription for this appointment already exists",
  "code": "DUPLICATE_PRESCRIPTION"
}
```

#### Parallel Creation Attempt
```bash
#!/bin/bash

DOCTOR_TOKEN="doctor_token"
APPOINTMENT_ID=123

# Create prescription in parallel (same appointment)
create_prescription() {
  curl -s -X POST http://localhost:8000/api/v1/prescriptions \
    -H "Authorization: Bearer $DOCTOR_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
      "appointment_id": '$APPOINTMENT_ID',
      "medications": [{"name": "Aspirin", "dosage": "100mg"}],
      "instructions": "Take once daily"
    }' | jq '.success, .error // .message'
}

export -f create_prescription

# Run 5 attempts in parallel
seq 1 5 | parallel -j 5 create_prescription

# Expected: 1 success, 4 conflicts
```

**Expected Results**:
```
✅ PASS: First attempt succeeds (201)
✅ PASS: All subsequent attempts fail (409)
✅ PASS: Only 1 prescription in database
✅ PASS: Error message is clear
```

**Verification**:
```bash
php artisan tinker

> Prescription::where('appointment_id', $appointmentId)->count()
# Expected: 1
```

---

### Test 4: Deadlock Detection and Retry

**Objective**: Verify automatic deadlock recovery

**Execution**:

This test requires intentional deadlock creation or monitoring existing deadlocks.

#### Monitor Deadlock Handling
```bash
# Watch logs for deadlock handling
tail -f storage/logs/laravel.log | grep "DEADLOCK\|Retry"

# In another terminal, create high-contention scenario
for i in {1..50}; do
  curl -X POST http://localhost:8000/api/v1/appointments \
    -H "Authorization: Bearer $TOKEN" \
    -d '{"doctor_id":1,"scheduled_at":"2024-12-25 10:00:00","type":"text_consultation"}' &
done

wait
```

**Expected Results**:
```
✅ PASS: Deadlock detected in logs (if deadlock occurs)
✅ PASS: Automatic retry with exponential backoff
✅ PASS: No deadlock errors in API responses
✅ PASS: All requests eventually succeed or fail with business logic error
```

**Check Retry Statistics**:
```php
// In tinker
$service = app(ConcurrentAccessService::class);
$stats = $service->getLockStatistics();

echo "Deadlock count: " . $stats['deadlock_count'] . "\n";
echo "Retries: " . $stats['retries'] . "\n";
echo "Average lock duration: " . $stats['avg_duration_ms'] . "ms\n";
```

---

### Test 5: Stress Test - High Concurrency

**Objective**: Performance under realistic load

**Setup**:
```yaml
# File: artillery-stress.yml
config:
  target: "http://localhost:8000"
  phases:
    - duration: 10
      arrivalRate: 5
      name: "Warm up"
    - duration: 30
      arrivalRate: 20
      name: "Sustained load"
    - duration: 10
      arrivalRate: 50
      name: "Peak load"

scenarios:
  - name: "Mixed Operations"
    flow:
      - post:
          url: "/api/v1/appointments"
          headers:
            Authorization: "Bearer {{ $tokens[randomInt(0, 4)] }}"
          json:
            doctor_id: "{{ randomInt(1, 10) }}"
            scheduled_at: "{{ $timestamp }}"
            type: "text_consultation"

      - put:
          url: "/api/v1/appointments/{{ $appointmentId }}"
          json:
            status: "confirmed"

      - post:
          url: "/api/v1/prescriptions"
          json:
            appointment_id: "{{ $appointmentId }}"
            medications: [{"name": "Test"}]
```

**Execution**:
```bash
artillery run artillery-stress.yml --output report.json
artillery report report.json
```

**Expected Results**:
```
✅ PASS: P95 response time < 500ms
✅ PASS: P99 response time < 1000ms
✅ PASS: Error rate < 0.5%
✅ PASS: No deadlock errors
✅ PASS: Database remains consistent
```

---

### Test 6: Database Consistency Check

**Objective**: Verify no invalid states exist after concurrent operations

**Execution**:

```bash
php artisan tinker

# Check 1: No double-booked appointments
Appointment::groupBy(['doctor_id', 'scheduled_at'])
  ->havingRaw('count(*) > 1')
  ->get()
  ->count()
# Expected: 0

# Check 2: All appointments have valid status
Appointment::whereNotIn('status', ['pending', 'confirmed', 'in_progress', 'completed', 'rejected', 'cancelled'])
  ->count()
# Expected: 0

# Check 3: All prescriptions reference completed appointments
Prescription::join('appointments', 'prescriptions.appointment_id', '=', 'appointments.id')
  ->where('appointments.status', '!=', 'completed')
  ->count()
# Expected: 0 (or acceptable number)

# Check 4: No duplicate prescriptions per appointment
Prescription::groupBy('appointment_id')
  ->havingRaw('count(*) > 1')
  ->count()
# Expected: 0

# Check 5: All ratings for completed consultations
Rating::join('konsultasi', 'ratings.konsultasi_id', '=', 'konsultasi.id')
  ->where('konsultasi.status', '!=', 'completed')
  ->count()
# Expected: 0
```

**Expected Results**:
```
✅ PASS: No orphaned or inconsistent records
✅ PASS: All foreign key relationships valid
✅ PASS: No duplicate records
✅ PASS: Status transitions are valid
```

---

## Automated Test Script

Create file: `tests/Feature/ConcurrentAccessTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $doctor;
    protected $patient1;
    protected $patient2;
    protected $patientToken1;
    protected $patientToken2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->create(['role' => 'dokter']);
        $this->patient1 = User::factory()->create(['role' => 'pasien']);
        $this->patient2 = User::factory()->create(['role' => 'pasien']);

        $this->patientToken1 = $this->patient1->createToken('test')->plainTextToken;
        $this->patientToken2 = $this->patient2->createToken('test')->plainTextToken;
    }

    public function test_double_booking_prevention()
    {
        $scheduledAt = now()->addDay()->setHour(10)->setMinute(0)->second(0);

        $response1 = $this->actingAs($this->patient1)
            ->postJson('/api/v1/appointments', [
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'type' => 'text_consultation',
            ]);

        $response1->assertStatus(201);

        $response2 = $this->actingAs($this->patient2)
            ->postJson('/api/v1/appointments', [
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'type' => 'text_consultation',
            ]);

        $response2->assertStatus(409);
        
        // Verify only one appointment created
        $this->assertEquals(
            1,
            Appointment::where('doctor_id', $this->doctor->id)
                ->where('scheduled_at', $scheduledAt)
                ->count()
        );
    }

    public function test_invalid_status_transition()
    {
        $appointment = Appointment::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient1->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->doctor)
            ->putJson("/api/v1/appointments/{$appointment->id}", [
                'status' => 'pending',  // Invalid: completed -> pending
            ]);

        $response->assertStatus(422);
    }

    public function test_duplicate_prescription_prevention()
    {
        $appointment = Appointment::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient1->id,
            'status' => 'completed',
        ]);

        $response1 = $this->actingAs($this->doctor)
            ->postJson('/api/v1/prescriptions', [
                'appointment_id' => $appointment->id,
                'medications' => [
                    ['name' => 'Aspirin', 'dosage' => '500mg'],
                ],
            ]);

        $response1->assertStatus(201);

        $response2 = $this->actingAs($this->doctor)
            ->postJson('/api/v1/prescriptions', [
                'appointment_id' => $appointment->id,
                'medications' => [
                    ['name' => 'Paracetamol', 'dosage' => '1000mg'],
                ],
            ]);

        $response2->assertStatus(409);
    }
}
```

**Run Tests**:
```bash
php artisan test tests/Feature/ConcurrentAccessTest.php

# Or with coverage
php artisan test tests/Feature/ConcurrentAccessTest.php --coverage
```

---

## Checklist

### Manual Testing
- [ ] Test 1: Double-booking prevention
- [ ] Test 2: Status update race condition
- [ ] Test 3: Duplicate prescription prevention
- [ ] Test 4: Deadlock detection and retry
- [ ] Test 5: Stress test with high concurrency
- [ ] Test 6: Database consistency check

### Automated Testing
- [ ] Run `ConcurrentAccessTest.php`
- [ ] Verify all tests pass
- [ ] Check code coverage > 80%

### Performance Validation
- [ ] P95 response time < 500ms
- [ ] P99 response time < 1000ms
- [ ] Error rate < 0.5%
- [ ] No deadlock errors in logs

### Database Verification
- [ ] No double-booked appointments
- [ ] No invalid status transitions
- [ ] No duplicate prescriptions
- [ ] No orphaned records
- [ ] Referential integrity maintained

---

## Troubleshooting

### Issue: "Lock wait timeout exceeded"
**Solution**:
```bash
# Increase lock timeout
# In .env or config/database.php
DB_LOCK_TIMEOUT=30  # seconds

# Or in code
DB::statement("SET innodb_lock_wait_timeout = 30");
```

### Issue: Deadlock errors in responses
**Solution**:
- Ensure `executeWithRetry()` is being called
- Check consistent lock ordering
- Review transaction scope

### Issue: Slow response times under load
**Solution**:
```bash
# 1. Check long-running queries
SHOW PROCESSLIST;

# 2. Verify indexes on frequently locked tables
SHOW INDEX FROM appointments;

# 3. Check lock contention
SHOW ENGINE INNODB STATUS;

# 4. Consider connection pooling
# Update config/database.php for connection pool settings
```

---

**Status**: Ready for comprehensive testing  
**Priority**: HIGH - Validates production readiness
