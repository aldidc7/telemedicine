## 🧪 COMPREHENSIVE TESTING STRATEGY & IMPLEMENTATION GUIDE

### Untuk Aplikasi Telemedicine Phase 6
**Status:** Ready to Execute
**Coverage Target:** 80%+
**Total Test Cases:** 100+

---

## 📊 TESTING PYRAMID

```
                    /\
                   /  \
                  / E2E \          Edge Cases, Full Workflows
                 /______\
                /        \
               /          \
              / Integration \     Multiple Components, Payment, Notifications
             /______    ____\
            /            \
           /              \
          / Unit Tests    \  Individual Functions, Calculations
         /                \
        /__________________\
```

### Distribution Target
- Unit Tests: 50% (50 tests)
- Integration Tests: 30% (30 tests)
- E2E/Feature Tests: 20% (20 tests)
- **Total: 100 test cases**

---

## 1️⃣ UNIT TESTS

### 1.1 Model Tests

```php
// tests/Unit/Models/ConsultationTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Consultation;
use App\Models\Dokter;
use App\Models\Pasien;

class ConsultationTest extends TestCase {
    
    /** @test */
    public function consultation_can_be_created() {
        $consultation = Consultation::factory()->create();
        $this->assertInstanceOf(Consultation::class, $consultation);
    }

    /** @test */
    public function consultation_has_correct_relationships() {
        $consultation = Consultation::factory()->create();
        
        $this->assertInstanceOf(Dokter::class, $consultation->doctor);
        $this->assertInstanceOf(Pasien::class, $consultation->patient);
    }

    /** @test */
    public function consultation_status_transitions_correctly() {
        $consultation = Consultation::factory()->create(['status' => 'pending']);
        
        $consultation->update(['status' => 'confirmed']);
        $this->assertEquals('confirmed', $consultation->fresh()->status);
    }

    /** @test */
    public function consultation_can_calculate_duration() {
        $consultation = Consultation::factory()->create([
            'scheduled_time' => now(),
            'completed_at' => now()->addHours(1),
        ]);
        
        $duration = $consultation->duration_minutes;
        $this->assertEquals(60, $duration);
    }
}

// tests/Unit/Models/PaymentTransactionTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PaymentTransaction;

class PaymentTransactionTest extends TestCase {
    
    /** @test */
    public function payment_splits_correctly_70_30() {
        $transaction = PaymentTransaction::factory()->create([
            'amount' => 100000,
        ]);
        
        $this->assertEquals(70000, $transaction->doctor_amount);
        $this->assertEquals(30000, $transaction->platform_fee);
        $this->assertEquals(100000, $transaction->doctor_amount + $transaction->platform_fee);
    }

    /** @test */
    public function payment_transaction_statuses_are_valid() {
        $transaction = PaymentTransaction::factory()->create();
        
        $validStatuses = ['pending', 'completed', 'failed', 'refunded'];
        $this->assertContains($transaction->status, $validStatuses);
    }
}

// tests/Unit/Models/DoctorVerificationTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\DoctorVerification;
use App\Models\Dokter;

class DoctorVerificationTest extends TestCase {
    
    /** @test */
    public function doctor_is_verified_only_when_all_requirements_met() {
        $verification = DoctorVerification::factory()->create([
            'sip_verified' => true,
            'str_verified' => true,
            'medical_license_verified' => true,
        ]);
        
        $this->assertTrue($verification->isFullyVerified());
    }

    /** @test */
    public function doctor_is_not_verified_if_missing_one_requirement() {
        $verification = DoctorVerification::factory()->create([
            'sip_verified' => true,
            'str_verified' => false,
            'medical_license_verified' => true,
        ]);
        
        $this->assertFalse($verification->isFullyVerified());
    }

    /** @test */
    public function verification_expiry_is_tracked() {
        $verification = DoctorVerification::factory()->create([
            'expires_at' => now()->addMonths(6),
        ]);
        
        $this->assertFalse($verification->isExpired());
        
        $verification->update(['expires_at' => now()->subDays(1)]);
        $this->assertTrue($verification->isExpired());
    }
}

// tests/Unit/Models/ConsentLogTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ConsentLog;

class ConsentLogTest extends TestCase {
    
    /** @test */
    public function consent_is_recorded_with_timestamp() {
        $consent = ConsentLog::factory()->create();
        
        $this->assertNotNull($consent->created_at);
        $this->assertTrue($consent->created_at->isToday());
    }

    /** @test */
    public function consent_withdrawal_is_tracked() {
        $consent = ConsentLog::factory()->create([
            'withdrawn_at' => null,
        ]);
        
        $this->assertNull($consent->withdrawn_at);
        
        $consent->withdraw();
        $this->assertNotNull($consent->fresh()->withdrawn_at);
    }
}
```

### 1.2 Service Tests

```php
// tests/Unit/Services/PaymentServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentService;
use App\Models\{Consultation, PaymentTransaction};

class PaymentServiceTest extends TestCase {
    
    private PaymentService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(PaymentService::class);
    }

    /** @test */
    public function payment_intent_creation_generates_unique_transaction_id() {
        $consultation = Consultation::factory()->create();
        
        $paymentData = $this->service->createPaymentIntent($consultation);
        
        $this->assertNotNull($paymentData['transaction_id']);
        $this->assertTrue(str_starts_with($paymentData['transaction_id'], 'TXN-'));
    }

    /** @test */
    public function commission_calculation_is_accurate() {
        $consultation = Consultation::factory()->create(['fee' => 1000]);
        
        $transaction = PaymentTransaction::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'amount' => 1000,
            'platform_fee' => 300,
            'doctor_amount' => 700,
        ]);
        
        $this->assertEquals(300, $transaction->platform_fee);
        $this->assertEquals(700, $transaction->doctor_amount);
    }

    /** @test */
    public function refund_reverses_transaction_amount() {
        $transaction = PaymentTransaction::factory()->create([
            'amount' => 1000,
            'status' => 'completed',
        ]);
        
        $originalAmount = $transaction->amount;
        $this->service->processRefund($transaction, 'Test refund');
        
        $refund = $transaction->refunds()->first();
        $this->assertEquals($originalAmount, $refund->amount);
    }
}

// tests/Unit/Services/AnalyticsServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\{Consultation, Dokter};

class AnalyticsServiceTest extends TestCase {
    
    private AnalyticsService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(AnalyticsService::class);
    }

    /** @test */
    public function analytics_counts_total_consultations_correctly() {
        Consultation::factory()->count(10)->create();
        
        $analytics = $this->service->getOverallAnalytics();
        
        $this->assertEquals(10, $analytics['total_consultations']);
    }

    /** @test */
    public function analytics_calculates_revenue_correctly() {
        Consultation::factory()->count(5)->create([
            'fee' => 100000,
            'status' => 'completed',
        ]);
        
        $analytics = $this->service->getOverallAnalytics();
        
        $this->assertEquals(500000, $analytics['total_revenue']);
    }

    /** @test */
    public function doctor_metrics_are_accurate() {
        $doctor = Dokter::factory()->create();
        Consultation::factory()->count(5)->create([
            'doctor_id' => $doctor->id,
            'status' => 'completed',
        ]);
        
        $metrics = $this->service->getDoctorMetrics($doctor->id);
        
        $this->assertEquals(5, $metrics['total_consultations']);
    }

    /** @test */
    public function analytics_caches_results() {
        Consultation::factory()->count(5)->create();
        
        $analytics1 = $this->service->getOverallAnalytics();
        
        // Change database
        Consultation::factory()->count(5)->create();
        
        // Should return cached result
        $analytics2 = $this->service->getOverallAnalytics();
        
        $this->assertEquals($analytics1['total_consultations'], $analytics2['total_consultations']);
    }
}

// tests/Unit/Services/ComplianceServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ComplianceService;
use App\Models\{AuditLog, ConsentLog};

class ComplianceServiceTest extends TestCase {
    
    private ComplianceService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(ComplianceService::class);
    }

    /** @test */
    public function audit_log_counts_user_actions() {
        AuditLog::factory()->count(10)->create([
            'user_id' => auth()->id(),
            'action' => 'login',
        ]);
        
        $logs = $this->service->getAuditLogsFor(auth()->user());
        
        $this->assertCount(10, $logs);
    }

    /** @test */
    public function consent_tracking_is_accurate() {
        ConsentLog::factory()->count(5)->create([
            'consent_type' => 'data_processing',
        ]);
        
        $consentReport = $this->service->getConsentReport();
        
        $this->assertEquals(5, $consentReport['data_processing_count']);
    }

    /** @test */
    public function compliance_report_identifies_missing_consents() {
        $patient = Pasien::factory()->create();
        ConsentLog::factory()->create([
            'patient_id' => $patient->id,
            'consent_type' => 'data_processing',
        ]);
        
        $violations = $this->service->findComplianceViolations();
        
        // Should flag missing marketing consent
        $this->assertTrue(
            $violations->contains(fn($v) => $v->type === 'missing_consent')
        );
    }
}
```

---

## 2️⃣ INTEGRATION TESTS

### 2.1 Payment Flow

```php
// tests/Integration/PaymentFlowTest.php
namespace Tests\Integration;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\{Consultation, PaymentTransaction, Invoice};

class PaymentFlowTest extends TestCase {
    
    /** @test */
    public function complete_payment_flow() {
        // 1. Create appointment
        $consultation = Consultation::factory()->create([
            'status' => 'pending',
            'fee' => 100000,
        ]);
        
        Sanctum::actingAs($consultation->patient->user);
        
        // 2. Initiate payment
        $response = $this->postJson('/api/v1/payments/initiate', [
            'consultation_id' => $consultation->id,
        ]);
        
        $response->assertStatus(200);
        $clientSecret = $response['data']['client_secret'];
        
        // 3. Verify payment transaction created
        $transaction = PaymentTransaction::where('consultation_id', $consultation->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('pending', $transaction->status);
        
        // 4. Confirm payment (simulate Stripe callback)
        $confirmResponse = $this->postJson('/api/v1/payments/confirm', [
            'client_secret' => $clientSecret,
            'payment_intent' => 'pi_test_success',
        ]);
        
        $confirmResponse->assertStatus(200);
        
        // 5. Verify transaction completed
        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->completed_at);
        
        // 6. Verify invoice generated
        $invoice = Invoice::where('payment_transaction_id', $transaction->id)->first();
        $this->assertNotNull($invoice);
        
        // 7. Verify consultation status updated
        $consultation->refresh();
        $this->assertEquals('confirmed', $consultation->status);
        
        // 8. Verify commission calculated correctly
        $this->assertEquals(70000, $transaction->doctor_amount);
        $this->assertEquals(30000, $transaction->platform_fee);
    }

    /** @test */
    public function refund_flow() {
        // Create completed transaction
        $transaction = PaymentTransaction::factory()->create([
            'status' => 'completed',
            'amount' => 100000,
            'doctor_amount' => 70000,
            'platform_fee' => 30000,
        ]);
        
        Sanctum::actingAs($transaction->patient->user);
        
        // Request refund
        $response = $this->postJson('/api/v1/refunds', [
            'transaction_id' => $transaction->id,
            'reason' => 'Patient requested cancellation',
        ]);
        
        $response->assertStatus(200);
        
        // Verify refund created
        $this->assertCount(1, $transaction->refunds);
        
        // Verify transaction marked as refunded
        $transaction->refresh();
        $this->assertEquals('refunded', $transaction->status);
    }
}

// tests/Integration/NotificationFlowTest.php
namespace Tests\Integration;

use Tests\TestCase;
use App\Models\Consultation;
use Illuminate\Support\Facades\Notification;

class NotificationFlowTest extends TestCase {
    
    /** @test */
    public function appointment_notification_sent_to_patient() {
        Notification::fake();
        
        $consultation = Consultation::factory()->create();
        
        event(new ConsultationCreated($consultation));
        
        Notification::assertSentTo(
            $consultation->patient->user,
            AppointmentConfirmedNotification::class
        );
    }

    /** @test */
    public function appointment_reminder_scheduled() {
        $consultation = Consultation::factory()->create([
            'scheduled_time' => now()->addDay(),
        ]);
        
        $this->artisan('schedule:run');
        
        // Verify job was dispatched
        $this->assertTrue(AppointmentReminder::wasDispatched());
    }

    /** @test */
    public function sms_sent_along_with_email() {
        Notification::fake();
        
        $consultation = Consultation::factory()->create();
        
        $consultation->patient->user->notify(new AppointmentReminder($consultation));
        
        // Verify both SMS and email sent
        Notification::assertSentTo(
            $consultation->patient->user,
            AppointmentReminder::class,
            function($notification) {
                return in_array('sms', $notification->via(null));
            }
        );
    }
}
```

---

## 3️⃣ FEATURE/E2E TESTS

### 3.1 Video Consultation Flow

```php
// tests/Feature/VideoConsultationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\{Consultation, VideoConsultation};

class VideoConsultationTest extends TestCase {
    
    /** @test */
    public function doctor_can_initiate_video_call() {
        $consultation = Consultation::factory()->create([
            'status' => 'confirmed',
        ]);
        
        Sanctum::actingAs($consultation->doctor->user);
        
        $response = $this->postJson(
            "/api/v1/consultations/{$consultation->id}/video/initiate"
        );
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['room_id', 'jitsi_server', 'token'],
        ]);
        
        // Verify video consultation created
        $videoConsultation = VideoConsultation::where(
            'consultation_id',
            $consultation->id
        )->first();
        
        $this->assertNotNull($videoConsultation);
        $this->assertNotNull($videoConsultation->started_at);
    }

    /** @test */
    public function patient_can_join_video_call() {
        $consultation = Consultation::factory()->create();
        
        VideoConsultation::create([
            'consultation_id' => $consultation->id,
            'room_id' => 'test-room-123',
            'started_at' => now(),
        ]);
        
        Sanctum::actingAs($consultation->patient->user);
        
        $response = $this->getJson(
            "/api/v1/consultations/{$consultation->id}/video/status"
        );
        
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'is_active' => true,
            ],
        ]);
    }

    /** @test */
    public function video_call_duration_tracked() {
        $consultation = Consultation::factory()->create();
        
        $videoConsultation = VideoConsultation::create([
            'consultation_id' => $consultation->id,
            'room_id' => 'test-room-123',
            'started_at' => now()->subMinutes(30),
        ]);
        
        Sanctum::actingAs($consultation->doctor->user);
        
        $response = $this->postJson(
            "/api/v1/consultations/{$consultation->id}/video/end"
        );
        
        $response->assertStatus(200);
        
        $videoConsultation->refresh();
        $this->assertGreaterThanOrEqual(30, $videoConsultation->duration_minutes);
    }

    /** @test */
    public function unauthorized_user_cannot_access_video() {
        $consultation = Consultation::factory()->create();
        $otherPatient = Pasien::factory()->create();
        
        Sanctum::actingAs($otherPatient->user);
        
        $response = $this->postJson(
            "/api/v1/consultations/{$consultation->id}/video/initiate"
        );
        
        $response->assertStatus(403);
    }
}
```

### 3.2 Prescription Download

```php
// tests/Feature/PrescriptionTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Prescription;

class PrescriptionTest extends TestCase {
    
    /** @test */
    public function patient_can_download_prescription_pdf() {
        $prescription = Prescription::factory()->create();
        
        Sanctum::actingAs($prescription->patient->user);
        
        $response = $this->getJson(
            "/api/v1/prescriptions/{$prescription->id}/download"
        );
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function doctor_can_view_prescription() {
        $prescription = Prescription::factory()->create();
        
        Sanctum::actingAs($prescription->doctor->user);
        
        $response = $this->getJson(
            "/api/v1/prescriptions/{$prescription->id}"
        );
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'diagnosis', 'medicines', 'valid_until'],
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_download() {
        $prescription = Prescription::factory()->create();
        $otherPatient = Pasien::factory()->create();
        
        Sanctum::actingAs($otherPatient->user);
        
        $response = $this->getJson(
            "/api/v1/prescriptions/{$prescription->id}/download"
        );
        
        $response->assertStatus(403);
    }

    /** @test */
    public function pdf_is_generated_on_first_download() {
        $prescription = Prescription::factory()->create([
            'pdf_path' => null,
        ]);
        
        Sanctum::actingAs($prescription->patient->user);
        
        $response = $this->getJson(
            "/api/v1/prescriptions/{$prescription->id}/download"
        );
        
        $response->assertStatus(200);
        
        $prescription->refresh();
        $this->assertNotNull($prescription->pdf_path);
    }
}
```

---

## 4️⃣ SECURITY TESTS

```php
// tests/Feature/SecurityTest.php
namespace Tests\Feature;

use Tests\TestCase;

class SecurityTest extends TestCase {
    
    /** @test */
    public function sql_injection_attempt_is_blocked() {
        $response = $this->getJson(
            "/api/v1/doctors?name=" . urlencode("'; DROP TABLE doctors; --")
        );
        
        $response->assertStatus(400);
        
        // Verify table still exists
        $this->assertTrue(Schema::hasTable('dokter'));
    }

    /** @test */
    public function xss_payload_is_sanitized() {
        $response = $this->postJson('/api/v1/consultations', [
            'notes' => '<script>alert("xss")</script>',
        ]);
        
        $consultation = Consultation::first();
        $this->assertStringNotContainsString('<script>', $consultation->notes);
    }

    /** @test */
    public function csrf_protection_enabled() {
        $response = $this->postJson('/api/v1/consultations', [], [
            'X-CSRF-TOKEN' => 'invalid_token',
        ]);
        
        $response->assertStatus(419);
    }

    /** @test */
    public function rate_limiting_enforced() {
        $this->withoutMiddleware(); // Remove rate limiting temporarily to test
        
        // Make 101 requests
        for ($i = 0; $i <= 100; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ]);
        }
        
        // 101st request should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);
        
        $response->assertStatus(429);
    }

    /** @test */
    public function password_reset_token_valid_once_only() {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        
        // First use
        $response1 = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
        $response1->assertStatus(200);
        
        // Second use with same token should fail
        $response2 = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);
        $response2->assertStatus(400);
    }

    /** @test */
    public function patient_cannot_access_other_patient_records() {
        $patient1 = Pasien::factory()->create();
        $patient2 = Pasien::factory()->create();
        
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient2->id,
        ]);
        
        Sanctum::actingAs($patient1->user);
        
        $response = $this->getJson(
            "/api/v1/consultations/{$consultation->id}"
        );
        
        $response->assertStatus(403);
    }

    /** @test */
    public function doctor_cannot_modify_other_doctor_data() {
        $doctor1 = Dokter::factory()->create();
        $doctor2 = Dokter::factory()->create();
        
        Sanctum::actingAs($doctor1->user);
        
        $response = $this->patchJson(
            "/api/v1/doctors/{$doctor2->id}",
            ['commission_rate' => 50],
        );
        
        $response->assertStatus(403);
    }
}
```

---

## 5️⃣ COMPLIANCE & DATA INTEGRITY TESTS

```php
// tests/Feature/ComplianceTest.php
namespace Tests\Feature;

use Tests\TestCase;

class ComplianceTest extends TestCase {
    
    /** @test */
    public function audit_log_is_immutable() {
        $log = AuditLog::factory()->create([
            'action' => 'delete_record',
        ]);
        
        // Attempt to modify
        $log->update(['action' => 'create_record']);
        
        // Should still be original value
        $this->assertEquals('delete_record', $log->fresh()->action);
    }

    /** @test */
    public function consent_tracking_is_required() {
        $patient = Pasien::factory()->create();
        
        // Verify consent log created
        $this->assertTrue(
            ConsentLog::where('patient_id', $patient->id)->exists()
        );
    }

    /** @test */
    public function doctor_verification_status_validated() {
        $doctor = Dokter::factory()->create();
        
        $verification = DoctorVerification::create([
            'doctor_id' => $doctor->id,
            'sip_verified' => true,
            'str_verified' => false,
            'medical_license_verified' => false,
        ]);
        
        $this->assertFalse($verification->isFullyVerified());
    }

    /** @test */
    public function medical_record_data_encryption() {
        $consultation = Consultation::factory()->create([
            'diagnosis' => 'Confidential diagnosis',
        ]);
        
        // Verify data is encrypted in database
        $rawData = DB::select(
            'SELECT `diagnosis` FROM consultations WHERE id = ?',
            [$consultation->id]
        )[0];
        
        // Raw database value should be encrypted
        $this->assertNotEquals('Confidential diagnosis', $rawData->diagnosis);
    }

    /** @test */
    public function data_retention_policy_enforced() {
        $oldConsultation = Consultation::factory()->create([
            'deleted_at' => now()->subYears(11), // 11 years old
        ]);
        
        // Should be purged
        $this->artisan('data:purge-old-records');
        
        $this->assertNull(Consultation::find($oldConsultation->id));
    }

    /** @test */
    public function financial_records_are_accurate_and_immutable() {
        $transaction = PaymentTransaction::create([
            'amount' => 100000,
            'doctor_amount' => 70000,
            'platform_fee' => 30000,
            'status' => 'completed',
        ]);
        
        // Verify total
        $this->assertEquals(
            100000,
            $transaction->doctor_amount + $transaction->platform_fee
        );
        
        // Attempt to modify amounts (should fail or be audited)
        $transaction->update([
            'doctor_amount' => 80000, // Try to change
        ]);
        
        // Should have audit log
        $this->assertTrue(
            AuditLog::where('model', 'PaymentTransaction')
                ->where('model_id', $transaction->id)
                ->exists()
        );
    }
}
```

---

## 6️⃣ RUNNING TESTS

### Setup
```bash
# Install PHPUnit
composer install

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/VideoConsultationTest.php

# Run with coverage
php artisan test --coverage

# Run only unit tests
php artisan test tests/Unit

# Run only feature tests
php artisan test tests/Feature
```

### GitHub Actions CI/CD
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: test
          MYSQL_ROOT_PASSWORD: root
        options: >-
          --health-cmd="mysqladmin ping -h localhost"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mysql
      
      - name: Install dependencies
        run: composer install
      
      - name: Create .env
        run: cp .env.example .env
      
      - name: Generate key
        run: php artisan key:generate
      
      - name: Run migrations
        run: php artisan migrate
      
      - name: Run tests with coverage
        run: php artisan test --coverage

      - name: Upload coverage
        uses: codecov/codecov-action@v2
```

---

## 7️⃣ COVERAGE TARGETS

```
Overall Coverage: 80%+

By Component:
- Controllers: 75%+
- Services: 85%+
- Models: 90%+
- Middleware: 80%+
- Helpers: 85%+

Critical Paths:
- Authentication: 95%
- Payment Processing: 95%
- Medical Data Access: 95%
- Compliance & Audit: 90%
```

---

## ✅ TEST CHECKLIST

- [ ] All unit tests passing
- [ ] All integration tests passing
- [ ] All feature tests passing
- [ ] Security tests passing
- [ ] Code coverage > 80%
- [ ] No skipped tests
- [ ] CI/CD pipeline green
- [ ] Performance tests passing
- [ ] Load tests passing
- [ ] Documentation complete

---

**Total Test Implementation Time: 5-7 days**
**Expected Coverage: 85%+**
**Maintained: Ongoing with each feature**
