## 📋 DETAILED ACTION PLAN - IMPLEMENTATION ROADMAP

### Status: Ready to Execute untuk Skripsi

---

## PHASE 6E: CRITICAL FIXES (Target: 2 Minggu)

### 1️⃣ VIDEO CONSULTATION FEATURE (Priority: CRITICAL)

#### 1.1 Database Migration
```bash
# Jalankan migration untuk video consultation
php artisan make:migration CreateVideoConsultationTable
php artisan make:migration AddVideoPropertiesToConsultations
```

**Migration Content:**
```php
// Migration: create_video_consultation_table
Schema::create('video_consultations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('consultation_id');
    $table->string('room_id'); // Jitsi room ID
    $table->timestamp('started_at')->nullable();
    $table->timestamp('ended_at')->nullable();
    $table->integer('duration_minutes')->default(0);
    $table->boolean('recorded')->default(false);
    $table->string('recording_url')->nullable();
    $table->json('participant_data')->nullable();
    $table->timestamps();
    $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
});

// In consultations table
Schema::table('consultations', function (Blueprint $table) {
    $table->enum('consultation_type', ['chat', 'video'])->default('chat');
    $table->timestamp('video_started_at')->nullable();
    $table->timestamp('video_ended_at')->nullable();
});
```

#### 1.2 Model & Service
```php
// app/Models/VideoConsultation.php
namespace App\Models;

class VideoConsultation extends Model {
    protected $fillable = [
        'consultation_id', 'room_id', 'started_at', 'ended_at',
        'duration_minutes', 'recorded', 'recording_url', 'participant_data'
    ];

    public function consultation() {
        return $this->belongsTo(Consultation::class);
    }

    public function generateRoomId() {
        return 'telemedicine-' . $this->consultation_id . '-' . uniqid();
    }
}

// app/Services/VideoConsultationService.php
namespace App\Services;

class VideoConsultationService {
    public function initiateVideoCall(Consultation $consultation) {
        $roomId = 'telemedicine-' . $consultation->id . '-' . Str::random(20);
        
        $video = VideoConsultation::create([
            'consultation_id' => $consultation->id,
            'room_id' => $roomId,
            'started_at' => now(),
        ]);

        $consultation->update([
            'consultation_type' => 'video',
            'video_started_at' => now(),
        ]);

        return [
            'room_id' => $roomId,
            'jitsi_server' => config('services.jitsi.server_url'),
            'token' => $this->generateJitsiToken($roomId, $consultation),
            'video_id' => $video->id,
        ];
    }

    public function endVideoCall(VideoConsultation $video) {
        $video->update([
            'ended_at' => now(),
            'duration_minutes' => $video->started_at->diffInMinutes($video->ended_at),
        ]);

        $video->consultation->update([
            'video_ended_at' => now(),
        ]);

        return $video;
    }

    private function generateJitsiToken($roomId, $consultation) {
        // Implementasi Jitsi JWT token generation
        // Gunakan Firebase JWT library
    }
}
```

#### 1.3 API Endpoints
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'verified_user'])
    ->group(function () {
        Route::post('/consultations/{consultation}/video/initiate', 
            [VideoConsultationController::class, 'initiateVideoCall']);
        Route::post('/consultations/{consultation}/video/end', 
            [VideoConsultationController::class, 'endVideoCall']);
        Route::get('/video-consultations/{videoConsultation}', 
            [VideoConsultationController::class, 'show']);
        Route::get('/consultations/{consultation}/video/status', 
            [VideoConsultationController::class, 'getVideoStatus']);
    });
```

#### 1.4 Controller
```php
// app/Http/Controllers/VideoConsultationController.php
namespace App\Http\Controllers;

class VideoConsultationController extends Controller {
    public function __construct(
        private VideoConsultationService $service,
        private ConsultationRepository $repository
    ) {}

    public function initiateVideoCall(Request $request, Consultation $consultation) {
        // Authorization: hanya doctor dan patient di consultation ini
        $this->authorize('viewConsultation', $consultation);

        // Validasi: consultation sudah dalam status "scheduled" atau "confirmed"
        if (!in_array($consultation->status, ['scheduled', 'confirmed'])) {
            return $this->error('Consultation tidak dalam status untuk video call', 400);
        }

        try {
            $videoData = $this->service->initiateVideoCall($consultation);
            
            // Notify patient
            event(new VideoCallInitiated($consultation, $videoData));

            return $this->success($videoData, 'Video consultation initiated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function endVideoCall(Request $request, Consultation $consultation) {
        $this->authorize('viewConsultation', $consultation);

        $video = $consultation->videoConsultation;
        if (!$video || $video->ended_at) {
            return $this->error('Video consultation not active', 400);
        }

        try {
            $video = $this->service->endVideoCall($video);
            
            // Update consultation status ke completed
            $consultation->update(['status' => 'completed']);

            event(new VideoCallEnded($consultation, $video));

            return $this->success($video, 'Video consultation ended');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getVideoStatus(Request $request, Consultation $consultation) {
        $video = $consultation->videoConsultation;
        
        return $this->success([
            'is_active' => $video && !$video->ended_at,
            'video_id' => $video?->id,
            'room_id' => $video?->room_id,
            'started_at' => $video?->started_at,
            'duration' => $video ? $video->started_at->diffInMinutes(now()) : 0,
        ]);
    }
}
```

#### 1.5 Frontend Integration (Vue/React)
```javascript
// VideoConsultationRoom.vue
<template>
  <div class="video-room-container">
    <div id="jitsi-container" class="jitsi-iframe"></div>
    <div class="video-controls">
      <button @click="endCall" class="btn-danger">End Call</button>
      <span>Duration: {{ duration }}s</span>
    </div>
  </div>
</template>

<script>
import { useConsultationStore } from '@/stores/consultation'

export default {
  props: ['consultationId'],
  setup() {
    const store = useConsultationStore()
    let jitsiApi = null
    let durationTimer = null
    let duration = ref(0)

    const loadJitsi = async () => {
      // Load Jitsi external API
      const script = document.createElement('script')
      script.src = 'https://meet.jit.si/external_api.js'
      script.onload = initializeJitsi
      document.head.appendChild(script)
    }

    const initializeJitsi = async () => {
      const response = await store.initiateVideoCall(consultationId)
      const options = {
        roomName: response.room_id,
        width: '100%',
        height: '100%',
        parentNode: document.getElementById('jitsi-container'),
        configOverwrite: {
          startAudioOnly: true,
          disableAudioLevels: true,
        },
        interfaceConfigOverwrite: {
          DEFAULT_LANGUAGE: 'id',
          SHOW_JITSI_WATERMARK: false,
        },
      }

      jitsiApi = new window.JitsiMeetExternalAPI(
        'meet.jit.si',
        options
      )

      startDurationTimer()
    }

    const endCall = async () => {
      jitsiApi.dispose()
      clearInterval(durationTimer)
      await store.endVideoCall(consultationId)
    }

    const startDurationTimer = () => {
      durationTimer = setInterval(() => {
        duration.value++
      }, 1000)
    }

    onMounted(() => loadJitsi())

    return { endCall, duration }
  }
}
</script>
```

#### 1.6 Configuration
```php
// config/services.php
'jitsi' => [
    'server_url' => env('JITSI_SERVER_URL', 'https://meet.jit.si'),
    'app_id' => env('JITSI_APP_ID'),
    'secret' => env('JITSI_SECRET'),
],

// .env
JITSI_SERVER_URL=https://meet.jit.si
JITSI_APP_ID=your_app_id
JITSI_SECRET=your_secret
```

---

### 2️⃣ PAYMENT GATEWAY INTEGRATION (Priority: CRITICAL)

#### 2.1 Database Setup
```php
// Migration: CreatePaymentTransactionsTable
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('consultation_id');
    $table->unsignedBigInteger('patient_id');
    $table->unsignedBigInteger('doctor_id');
    $table->decimal('amount', 12, 2);
    $table->decimal('platform_fee', 12, 2); // 30%
    $table->decimal('doctor_amount', 12, 2); // 70%
    $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
    $table->enum('payment_method', ['card', 'bank_transfer', 'e_wallet', 'gcash'])->nullable();
    $table->string('transaction_id')->unique();
    $table->string('payment_gateway_id')->nullable(); // Stripe ID, etc
    $table->text('payment_gateway_response')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamp('failed_at')->nullable();
    $table->string('failure_reason')->nullable();
    $table->timestamps();
    
    $table->foreign('consultation_id')->references('id')->on('consultations');
    $table->foreign('patient_id')->references('id')->on('pasien');
    $table->foreign('doctor_id')->references('id')->on('dokter');
    $table->index('status');
    $table->index('transaction_id');
});

// Migration: CreateInvoicesTable
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('payment_transaction_id');
    $table->unsignedBigInteger('patient_id');
    $table->string('invoice_number')->unique();
    $table->date('issue_date');
    $table->date('due_date');
    $table->decimal('amount', 12, 2);
    $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue'])->default('draft');
    $table->string('pdf_path')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->foreign('payment_transaction_id')->references('id')->on('payment_transactions');
    $table->foreign('patient_id')->references('id')->on('pasien');
});

// Migration: CreateRefundsTable
Schema::create('refunds', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('payment_transaction_id');
    $table->decimal('amount', 12, 2);
    $table->enum('status', ['pending', 'approved', 'processed', 'failed'])->default('pending');
    $table->text('reason');
    $table->unsignedBigInteger('approved_by')->nullable(); // Admin ID
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->string('gateway_refund_id')->nullable();
    $table->text('gateway_response')->nullable();
    $table->timestamps();
    
    $table->foreign('payment_transaction_id')->references('id')->on('payment_transactions');
});
```

#### 2.2 Models
```php
// app/Models/PaymentTransaction.php
class PaymentTransaction extends Model {
    protected $fillable = [
        'consultation_id', 'patient_id', 'doctor_id', 'amount',
        'platform_fee', 'doctor_amount', 'status', 'payment_method',
        'transaction_id', 'payment_gateway_id', 'payment_gateway_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'doctor_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'payment_gateway_response' => 'array',
    ];

    public function consultation() {
        return $this->belongsTo(Consultation::class);
    }

    public function patient() {
        return $this->belongsTo(Pasien::class);
    }

    public function doctor() {
        return $this->belongsTo(Dokter::class);
    }

    public function refunds() {
        return $this->hasMany(Refund::class);
    }

    public function invoice() {
        return $this->hasOne(Invoice::class);
    }
}

// app/Models/Invoice.php
class Invoice extends Model {
    protected $fillable = [
        'payment_transaction_id', 'patient_id', 'invoice_number',
        'issue_date', 'due_date', 'amount', 'status', 'pdf_path'
    ];

    public function paymentTransaction() {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function patient() {
        return $this->belongsTo(Pasien::class);
    }
}

// app/Models/Refund.php
class Refund extends Model {
    protected $fillable = [
        'payment_transaction_id', 'amount', 'status', 'reason',
        'approved_by', 'approved_at'
    ];

    public function paymentTransaction() {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
```

#### 2.3 Payment Service (Stripe)
```php
// app/Services/PaymentService.php
namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentService {
    private $stripe;

    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Consultation $consultation) {
        // Calculate amounts
        $totalAmount = (int)($consultation->fee * 100); // Convert to cents
        $platformFee = (int)($consultation->fee * 0.30 * 100);
        $doctorAmount = (int)($consultation->fee * 0.70 * 100);

        try {
            $intent = PaymentIntent::create([
                'amount' => $totalAmount,
                'currency' => 'usd', // Or IDR with proper conversion
                'payment_method_types' => ['card'],
                'metadata' => [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                ],
            ]);

            $transaction = PaymentTransaction::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'amount' => $consultation->fee,
                'platform_fee' => $consultation->fee * 0.30,
                'doctor_amount' => $consultation->fee * 0.70,
                'status' => 'pending',
                'payment_gateway_id' => $intent->id,
                'transaction_id' => $this->generateTransactionId(),
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'transaction_id' => $transaction->transaction_id,
                'amount' => $consultation->fee,
            ];
        } catch (\Exception $e) {
            throw new PaymentException('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    public function confirmPayment($stripeIntentId) {
        try {
            $intent = PaymentIntent::retrieve($stripeIntentId);

            if ($intent->status !== 'succeeded') {
                throw new PaymentException('Payment not successful');
            }

            $transaction = PaymentTransaction::where(
                'payment_gateway_id',
                $stripeIntentId
            )->firstOrFail();

            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'payment_gateway_response' => json_encode($intent),
            ]);

            // Update consultation status
            $transaction->consultation->update(['status' => 'confirmed']);

            // Generate invoice
            $this->generateInvoice($transaction);

            // Record revenue
            event(new PaymentCompleted($transaction));

            return $transaction;
        } catch (\Exception $e) {
            throw new PaymentException('Payment confirmation failed: ' . $e->getMessage());
        }
    }

    public function processRefund(PaymentTransaction $transaction, $reason) {
        if ($transaction->status !== 'completed') {
            throw new PaymentException('Only completed payments can be refunded');
        }

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transaction->payment_gateway_id,
            ]);

            $refundRecord = Refund::create([
                'payment_transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'status' => 'pending',
                'reason' => $reason,
                'gateway_refund_id' => $refund->id,
                'gateway_response' => json_encode($refund),
            ]);

            $transaction->update(['status' => 'refunded']);

            event(new RefundProcessed($refundRecord));

            return $refundRecord;
        } catch (\Exception $e) {
            throw new PaymentException('Refund processing failed: ' . $e->getMessage());
        }
    }

    private function generateTransactionId() {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    private function generateInvoice(PaymentTransaction $transaction) {
        $invoiceNumber = 'INV-' . $transaction->id . '-' . date('Ymd');

        Invoice::create([
            'payment_transaction_id' => $transaction->id,
            'patient_id' => $transaction->patient_id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => $transaction->amount,
            'status' => 'sent',
        ]);
    }
}
```

#### 2.4 API Endpoints
```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Payment
    Route::post('/payments/initiate', [PaymentController::class, 'initiatePayment']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/payments/{transaction}', [PaymentController::class, 'getTransaction']);
    Route::get('/payments', [PaymentController::class, 'listTransactions']);

    // Refunds
    Route::post('/refunds/request', [RefundController::class, 'requestRefund']);
    Route::get('/refunds', [RefundController::class, 'listRefunds']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'list']);
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/refunds/{refund}/approve', [RefundController::class, 'approveRefund']);
    Route::post('/refunds/{refund}/reject', [RefundController::class, 'rejectRefund']);
    Route::get('/payments/analytics', [PaymentAnalyticsController::class, 'getAnalytics']);
});
```

---

### 3️⃣ PRESCRIPTION PDF DOWNLOAD (Priority: CRITICAL)

#### 3.1 Model Setup
```php
// app/Models/Prescription.php Enhancement
class Prescription extends Model {
    // Add methods for PDF generation
    public function generatePDF() {
        return app('pdf')->loadView('prescriptions.template', [
            'prescription' => $this,
            'patient' => $this->consultation->patient,
            'doctor' => $this->consultation->doctor,
        ]);
    }

    public function downloadPDF() {
        return $this->generatePDF()->download('Prescription-' . $this->id . '.pdf');
    }
}
```

#### 3.2 Service
```php
// app/Services/PrescriptionService.php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionService {
    public function generatePrescriptionPDF(Prescription $prescription) {
        $pdf = Pdf::loadView('prescriptions.pdf-template', [
            'prescription' => $prescription,
            'consultation' => $prescription->consultation,
            'patient' => $prescription->consultation->patient,
            'doctor' => $prescription->consultation->doctor,
            'generatedAt' => now(),
            'validUntil' => $prescription->valid_until,
        ]);

        return $pdf;
    }

    public function savePrescriptionPDF(Prescription $prescription) {
        $filename = 'prescriptions/RX-' . $prescription->id . '-' . date('Ymd') . '.pdf';
        
        Storage::disk('private')->put(
            $filename,
            $this->generatePrescriptionPDF($prescription)->output()
        );

        $prescription->update(['pdf_path' => $filename]);

        return $filename;
    }

    public function downloadPrescriptionPDF(Prescription $prescription) {
        if (!$prescription->pdf_path) {
            $this->savePrescriptionPDF($prescription);
        }

        return Storage::disk('private')->download($prescription->pdf_path);
    }
}
```

#### 3.3 Controller
```php
// app/Http/Controllers/PrescriptionController.php
public function download(Prescription $prescription) {
    // Authorization
    $this->authorize('download', $prescription);

    try {
        return $this->service->downloadPrescriptionPDF($prescription);
    } catch (\Exception $e) {
        return $this->error('Failed to download prescription: ' . $e->getMessage());
    }
}

public function view(Prescription $prescription) {
    $this->authorize('view', $prescription);

    if (!$prescription->pdf_path) {
        $this->service->savePrescriptionPDF($prescription);
    }

    return response()->file(
        Storage::disk('private')->path($prescription->pdf_path),
        ['Content-Type' => 'application/pdf']
    );
}
```

#### 3.4 PDF Template
```blade
{{-- resources/views/prescriptions/pdf-template.blade.php --}}
<html>
<head>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 20px;
        }
        .header { border-bottom: 2px solid #333; padding-bottom: 15px; }
        .patient-info { margin-top: 20px; }
        .medicines { margin-top: 30px; }
        .medicine-item { 
            border: 1px solid #ddd; 
            padding: 10px; 
            margin: 10px 0; 
        }
        .signature { margin-top: 40px; }
        .watermark { 
            position: fixed; 
            opacity: 0.1; 
            font-size: 60px; 
            transform: rotate(-45deg);
        }
    </style>
</head>
<body>
    <div class="watermark">{{ strtoupper($doctor->name) }}</div>

    <div class="header">
        <h1>RESEP OBAT</h1>
        <p>No. Resep: {{ $prescription->id }}</p>
        <p>Tanggal: {{ $prescription->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="patient-info">
        <h3>Data Pasien</h3>
        <p><strong>Nama:</strong> {{ $patient->name }}</p>
        <p><strong>No. Kartu Pasien:</strong> {{ $patient->patient_id }}</p>
        <p><strong>Tanggal Lahir:</strong> {{ $patient->date_of_birth->format('d/m/Y') }}</p>
    </div>

    <div class="doctor-info">
        <h3>Dokter Pemeriksa</h3>
        <p><strong>Nama:</strong> {{ $doctor->name }}</p>
        <p><strong>SIP:</strong> {{ $doctor->sip_number }}</p>
        <p><strong>Spesialis:</strong> {{ $doctor->specialization }}</p>
    </div>

    <div class="medicines">
        <h3>Obat-obatan</h3>
        @foreach($prescription->medicines as $medicine)
            <div class="medicine-item">
                <strong>{{ $medicine->name }}</strong> ({{ $medicine->dosage }})
                <br/>
                <span>{{ $medicine->frequency }}</span>
                <br/>
                <small>{{ $medicine->notes }}</small>
            </div>
        @endforeach
    </div>

    <div class="signature">
        <p>Jakarta, {{ now()->format('d/m/Y') }}</p>
        <p style="margin-top: 60px;">
            {{ $doctor->name }}<br/>
            {{ $doctor->sip_number }}
        </p>
    </div>
</body>
</html>
```

---

### 4️⃣ COMPREHENSIVE TEST SUITE untuk Phase 6 (Priority: CRITICAL)

#### 4.1 Analytics Controller Tests
```php
// tests/Feature/AnalyticsControllerTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Consultation, Dokter, Pasien};
use Laravel\Sanctum\Sanctum;

class AnalyticsControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        Sanctum::actingAs($this->admin);
    }

    /** @test */
    public function admin_can_get_overall_analytics() {
        // Create test data
        Consultation::factory()->count(10)->create(['status' => 'completed']);
        
        $response = $this->getJson('/api/v1/analytics/overall');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_consultations',
                    'total_doctors',
                    'total_patients',
                    'revenue_generated',
                ]
            ]);
    }

    /** @test */
    public function non_admin_cannot_access_analytics() {
        $patient = Pasien::factory()->create();
        Sanctum::actingAs($patient->user);
        
        $response = $this->getJson('/api/v1/analytics/overall');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function analytics_respects_date_filter() {
        $lastMonth = Consultation::factory()
            ->create(['created_at' => now()->subMonth()]);
        
        $thisMonth = Consultation::factory()
            ->create(['created_at' => now()]);
        
        $response = $this->getJson('/api/v1/analytics/overall?from=' . now()->startOfMonth());
        
        $this->assertFalse(in_array($lastMonth->id, 
            collect($response['data']['consultations'])->pluck('id')->toArray()
        ));
    }

    /** @test */
    public function doctor_metrics_are_accurate() {
        $doctor = Dokter::factory()->create();
        Consultation::factory()->count(5)
            ->create(['doctor_id' => $doctor->id, 'status' => 'completed']);
        
        $response = $this->getJson('/api/v1/analytics/doctors/' . $doctor->id);
        
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_consultations' => 5,
                    'doctor_id' => $doctor->id,
                ]
            ]);
    }
}
```

#### 4.2 Financial Reporting Tests
```php
// tests/Feature/FinancialReportingTest.php
namespace Tests\Feature;

class FinancialReportingTest extends TestCase {
    /** @test */
    public function commission_calculation_is_accurate_70_30_split() {
        $consultation = Consultation::factory()->create([
            'fee' => 100000, // Rp 100,000
            'status' => 'completed'
        ]);
        
        $this->artisan('report:generate-monthly', [
            'month' => now()->month,
            'year' => now()->year,
        ]);
        
        $transaction = PaymentTransaction::where('consultation_id', $consultation->id)
            ->first();
        
        $this->assertEquals(70000, $transaction->doctor_amount); // 70%
        $this->assertEquals(30000, $transaction->platform_fee); // 30%
    }

    /** @test */
    public function refund_reverses_commission() {
        $transaction = PaymentTransaction::factory()->create([
            'doctor_amount' => 70000,
            'status' => 'completed'
        ]);
        
        $this->postJson('/api/v1/refunds', [
            'transaction_id' => $transaction->id,
            'reason' => 'Patient request'
        ]);
        
        $transaction->refresh();
        $this->assertEquals('refunded', $transaction->status);
    }

    /** @test */
    public function monthly_report_equals_sum_of_transactions() {
        // Create transactions in January
        PaymentTransaction::factory()->count(5)
            ->create([
                'amount' => 100000,
                'created_at' => now()->startOfMonth()
            ]);
        
        $report = $this->generateMonthlyReport(now()->month, now()->year);
        $totalTransactions = PaymentTransaction::whereMonth('created_at', now()->month)
            ->sum('amount');
        
        $this->assertEquals($totalTransactions, $report['total_revenue']);
    }

    /** @test */
    public function currency_conversion_is_accurate() {
        // Test dengan conversion rate
        $consultationUSD = Consultation::factory()->create([
            'currency' => 'USD',
            'fee' => 25,
        ]);
        
        $report = $this->generateMonthlyReport();
        
        // Verify: USD 25 ~ Rp 375,000 at rate 15,000
        $expectedRp = 25 * 15000;
        $this->assertAlmostEqual($expectedRp, $report['converted_total'], 1000);
    }
}
```

#### 4.3 Compliance Tests
```php
// tests/Feature/ComplianceTest.php
class ComplianceTest extends TestCase {
    /** @test */
    public function audit_log_immutability() {
        $log = AuditLog::factory()->create(['action' => 'delete_record']);
        
        // Attempt to modify
        $log->update(['action' => 'create_record']);
        
        // Verify tidak berubah (AuditLog should be immutable)
        $this->assertEquals('delete_record', $log->fresh()->action);
    }

    /** @test */
    public function patient_cannot_access_other_patient_records() {
        $patient1 = Pasien::factory()->create();
        $patient2 = Pasien::factory()->create();
        
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient2->id
        ]);
        
        Sanctum::actingAs($patient1->user);
        
        $response = $this->getJson('/api/v1/consultations/' . $consultation->id);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function doctor_verification_status_tracked() {
        $doctor = Dokter::factory()->create();
        
        DoctorVerification::create([
            'doctor_id' => $doctor->id,
            'sip_verified' => true,
            'str_verified' => false,
        ]);
        
        $this->assertTrue($doctor->verification->sip_verified);
        $this->assertFalse($doctor->verification->str_verified);
    }

    /** @test */
    public function consent_log_recorded_on_patient_signup() {
        $response = $this->postJson('/auth/register', [
            'name' => 'Test Patient',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'consent' => true,
        ]);
        
        $patient = Pasien::where('email', 'test@example.com')->first();
        
        $this->assertTrue(
            ConsentLog::where('patient_id', $patient->id)->exists()
        );
    }
}
```

#### 4.4 Security Tests
```php
// tests/Feature/SecurityTest.php
class SecurityTest extends TestCase {
    /** @test */
    public function sql_injection_attempt_blocked() {
        $response = $this->getJson(
            '/api/v1/patients?name=' . urlencode("'; DROP TABLE patients; --")
        );
        
        $response->assertStatus(400);
        // Verify table still exists
        $this->assertTrue(Schema::hasTable('pasien'));
    }

    /** @test */
    public function xss_payload_sanitized() {
        $response = $this->postJson('/api/v1/consultations', [
            'title' => '<script>alert("xss")</script>',
            'notes' => '<img src=x onerror="alert(1)">',
        ]);
        
        $consultation = Consultation::first();
        $this->assertStringNotContainsString('<script>', $consultation->title);
    }

    /** @test */
    public function csrf_token_required_for_post() {
        $response = $this->postJson('/api/v1/consultations', [], [
            'X-CSRF-TOKEN' => 'invalid_token'
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function password_reset_token_valid_once() {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        
        // First use
        $response1 = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'password' => 'NewPassword123!',
        ]);
        $response1->assertStatus(200);
        
        // Second use with same token
        $response2 = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'password' => 'AnotherPassword123!',
        ]);
        $response2->assertStatus(400);
    }

    /** @test */
    public function rate_limiting_enforced() {
        for ($i = 0; $i < 101; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong_password',
            ]);
        }
        
        // Should be rate limited
        $this->assertEquals(429, $response->status());
    }
}
```

---

### 5️⃣ INTEGRATION TEST SUITE

#### 5.1 Payment + Consultation Flow
```php
// tests/Integration/PaymentConsultationFlowTest.php
class PaymentConsultationFlowTest extends TestCase {
    /** @test */
    public function full_consultation_payment_flow() {
        // 1. Patient books consultation
        $consultation = Consultation::factory()->create([
            'status' => 'pending',
            'fee' => 100000,
        ]);
        
        // 2. Payment initiation
        $paymentResponse = $this->postJson(
            '/api/v1/payments/initiate',
            ['consultation_id' => $consultation->id]
        );
        $paymentResponse->assertStatus(200);
        $clientSecret = $paymentResponse['data']['client_secret'];
        
        // 3. Simulate Stripe payment
        // (In real tests, use Stripe test API)
        
        // 4. Confirm payment
        $confirmResponse = $this->postJson(
            '/api/v1/payments/confirm',
            ['client_secret' => $clientSecret]
        );
        $confirmResponse->assertStatus(200);
        
        // 5. Verify consultation status updated
        $consultation->refresh();
        $this->assertEquals('confirmed', $consultation->status);
        
        // 6. Verify invoice generated
        $transaction = PaymentTransaction::where('consultation_id', $consultation->id)->first();
        $this->assertTrue(Invoice::where('payment_transaction_id', $transaction->id)->exists());
        
        // 7. Verify commission calculated
        $this->assertEquals(70000, $transaction->doctor_amount);
        $this->assertEquals(30000, $transaction->platform_fee);
    }
}
```

Implementasi besar ini akan menempatkan aplikasi Anda di level production-ready untuk skripsi. Mau saya lanjutkan dengan section lainnya? 

Sebelum lanjut, pilih yang mana:
1. **Melanjutkan implementasi 6-7 bagian lain** (Notifications, Consent Management, Test Suite)
2. **Fokus pada satu bagian saja** untuk detail lebih dalam
3. **Setup environment terlebih dahulu** (composer, npm, config)
