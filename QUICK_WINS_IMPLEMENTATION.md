## 🚀 QUICK WINS - IMPLEMENTASI INSTAN (1 Minggu)

### Prioritas: HIGH
**Target Waktu: 5-7 hari kerja**
**Hasil: 5 fitur penting yang meningkatkan score skripsi 30%**

---

## 1️⃣ API DOCUMENTATION & SWAGGER UI (1 hari)

### Masalah Saat Ini
```
- app/OpenAPI/ApiDocumentation.php ada tapi tidak di-serve
- Tidak ada /api/docs endpoint
- Postman collection tidak up-to-date untuk Phase 6
```

### Solusi

#### Step 1.1: Install Laravel OpenAPI Package
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

#### Step 1.2: Generate Swagger from Code
```php
// config/l5-swagger.php (auto-generated, update)
'paths' => [
    'docs' => 'api-docs',
    'annotations' => base_path('app'),
    'docs_yaml' => 'docs.yaml',
],

// config/l5-swagger.php - add basic info
'swagger' => [
    'api_path' => 'api-docs/swagger.yaml',
    'swagger_ui_path' => 'api/documentation',
    'route' => '/api/documentation',
    'ui' => [
        'display' => [
            'dark_mode' => false,
            'filter' => true,
            'show_summary' => true,
        ],
    ],
],
```

#### Step 1.3: Add Route
```php
// routes/api.php
Route::get('/documentation', function() {
    return view('swagger-ui');
});

// Or use L5Swagger built-in route
// Just add to middleware
```

#### Step 1.4: Add OpenAPI Annotations ke Controllers

```php
// app/Http/Controllers/VideoConsultationController.php

/**
 * @OA\Post(
 *     path="/api/v1/consultations/{consultation}/video/initiate",
 *     operationId="initiateVideoCall",
 *     tags={"Video Consultations"},
 *     summary="Initiate video consultation",
 *     description="Start a video call for a consultation",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="consultation",
 *         in="path",
 *         description="Consultation ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Video call initiated",
 *         @OA\JsonContent(
 *             @OA\Property(property="room_id", type="string"),
 *             @OA\Property(property="jitsi_server", type="string"),
 *             @OA\Property(property="token", type="string"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized"
 *     )
 * )
 */
public function initiateVideoCall(Request $request, Consultation $consultation) {
    // Implementation
}
```

#### Step 1.5: Generate & Serve
```bash
php artisan l5-swagger:generate
php artisan serve

# Access: http://localhost:8000/api/documentation
```

#### Step 1.6: Update Postman Collection
```json
{
  "info": {
    "name": "Telemedicine API - Phase 6 Complete",
    "description": "Complete API collection with video, payments, and compliance",
    "version": "1.0.0"
  },
  "item": [
    {
      "name": "Video Consultations",
      "item": [
        {
          "name": "Initiate Video Call",
          "request": {
            "method": "POST",
            "url": {
              "raw": "{{base_url}}/api/v1/consultations/1/video/initiate",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "consultations", "1", "video", "initiate"]
            },
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ]
          },
          "response": []
        }
      ]
    }
  ]
}
```

**Hasil:** ✅ Full API documentation accessible
**Time:** 2-3 jam

---

## 2️⃣ SMS NOTIFICATION SYSTEM (Twilio) (1 hari)

### Masalah Saat Ini
```
- Hanya email notifications
- Tidak ada SMS untuk appointment reminders
- Pasien tidak dapat SMS konfirmasi
```

### Solusi

#### Step 2.1: Install Twilio
```bash
composer require twilio/sdk
```

#### Step 2.2: Configuration
```php
// config/services.php
'twilio' => [
    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'phone_number' => env('TWILIO_PHONE_NUMBER'),
],

// .env
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=+1234567890
```

#### Step 2.3: SMS Notification Channel
```php
// app/Notifications/AppointmentReminder.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AppointmentReminder extends Notification {
    public function via($notifiable) {
        return ['sms', 'mail']; // Both SMS and email
    }

    public function toSms($notifiable) {
        $appointmentTime = $this->appointment->scheduled_time->format('d/m/Y H:i');
        
        return (new TwilioSmsMessage())
            ->content("Reminder: You have an appointment on {$appointmentTime} with {$this->appointment->doctor->name}. "
                . "Reply CONFIRM to confirm or CANCEL to cancel.");
    }

    public function toMail($notifiable) {
        return (new MailMessage())
            ->subject('Appointment Reminder')
            ->line("You have an appointment on {$this->appointment->scheduled_time->format('d/m/Y H:i')}")
            ->action('View Appointment', route('appointments.show', $this->appointment));
    }
}

// app/Channels/TwilioSmsChannel.php
namespace App\Channels;

use Twilio\Rest\Client;
use Illuminate\Notifications\Notification;

class TwilioSmsChannel {
    protected $twilio;

    public function __construct(Client $twilio) {
        $this->twilio = $twilio;
    }

    public function send($notifiable, Notification $notification) {
        $message = $notification->toSms($notifiable);

        if (!$phoneNumber = $notifiable->phone_number) {
            return;
        }

        return $this->twilio->messages->create(
            $phoneNumber,
            [
                'from' => config('services.twilio.phone_number'),
                'body' => $message->content,
            ]
        );
    }
}

// app/Notifications/Messages/TwilioSmsMessage.php
namespace App\Notifications\Messages;

class TwilioSmsMessage {
    public $content;

    public function content($content) {
        $this->content = $content;
        return $this;
    }
}
```

#### Step 2.4: Register Channel
```php
// app/Providers/AppServiceProvider.php
use App\Channels\TwilioSmsChannel;
use Twilio\Rest\Client;

public function register() {
    $this->app->bind(Client::class, function() {
        return new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
    });

    $this->app->make('notifications.manager')
        ->extend('sms', function($app) {
            return new TwilioSmsChannel($app[Client::class]);
        });
}
```

#### Step 2.5: Send SMS on Appointment Created
```php
// app/Models/Consultation.php
protected static function booted() {
    static::created(function($consultation) {
        // Send SMS to patient
        $consultation->patient->user->notify(
            new AppointmentReminder($consultation)
        );
    });
}
```

#### Step 2.6: Scheduled Reminder Job
```bash
php artisan make:job SendAppointmentReminders
```

```php
// app/Jobs/SendAppointmentReminders.php
namespace App\Jobs;

use App\Models\Consultation;
use App\Notifications\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAppointmentReminders implements ShouldQueue {
    use Queueable, SerializesModels, InteractsWithQueue;

    public function handle() {
        // Get consultations scheduled for tomorrow
        $tomorrow = now()->addDay()->startOfDay();
        $tomorrowEnd = $tomorrow->copy()->endOfDay();

        $consultations = Consultation::whereBetween('scheduled_time', [$tomorrow, $tomorrowEnd])
            ->where('status', 'confirmed')
            ->get();

        foreach ($consultations as $consultation) {
            $consultation->patient->user->notify(
                new AppointmentReminder($consultation, '1_day')
            );
        }

        // Get consultations in next hour
        $nowEnd = now()->copy()->addHour();

        $consultations = Consultation::whereBetween('scheduled_time', [now(), $nowEnd])
            ->where('status', 'confirmed')
            ->get();

        foreach ($consultations as $consultation) {
            $consultation->patient->user->notify(
                new AppointmentReminder($consultation, '1_hour')
            );
        }
    }
}

// Schedule in Console/Kernel.php
protected function schedule(Schedule $schedule) {
    $schedule->job(new SendAppointmentReminders::class)
        ->everyMinute();
}
```

**Hasil:** ✅ SMS notifications working
**Time:** 3-4 jam

---

## 3️⃣ PRESCRIPTION PDF DOWNLOAD (1 hari)

### Masalah Saat Ini
```
- Prescription model ada
- Tidak ada PDF generation
- Tidak ada download endpoint
```

### Solusi

#### Step 3.1: Install DomPDF
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

#### Step 3.2: Prescription Model Enhancement
```php
// app/Models/Prescription.php
protected $fillable = [
    'consultation_id', 'doctor_id', 'patient_id', 'diagnosis',
    'medicines', 'instructions', 'pdf_path', 'valid_until'
];

protected $casts = [
    'medicines' => 'array',
    'valid_until' => 'datetime',
];

public function consultation() {
    return $this->belongsTo(Consultation::class);
}

public function doctor() {
    return $this->belongsTo(Dokter::class);
}

public function patient() {
    return $this->belongsTo(Pasien::class);
}
```

#### Step 3.3: Service Class
```php
// app/Services/PrescriptionService.php
namespace App\Services;

use PDF;
use App\Models\Prescription;
use Illuminate\Support\Facades\Storage;

class PrescriptionService {
    public function generatePDFFile(Prescription $prescription) {
        $pdf = PDF::loadView('prescriptions.template', [
            'prescription' => $prescription,
            'doctor' => $prescription->doctor,
            'patient' => $prescription->patient,
            'consultation' => $prescription->consultation,
        ]);

        $filename = 'prescriptions/RX-' . $prescription->id . '-' . now()->format('Ymd') . '.pdf';
        Storage::disk('private')->put($filename, $pdf->output());

        $prescription->update(['pdf_path' => $filename]);

        return $filename;
    }

    public function getPrescriptionPDF(Prescription $prescription) {
        if (!$prescription->pdf_path || !Storage::disk('private')->exists($prescription->pdf_path)) {
            $this->generatePDFFile($prescription);
        }

        return Storage::disk('private')->download($prescription->pdf_path);
    }

    public function viewPrescriptionPDF(Prescription $prescription) {
        if (!$prescription->pdf_path || !Storage::disk('private')->exists($prescription->pdf_path)) {
            $this->generatePDFFile($prescription);
        }

        return response()->file(
            Storage::disk('private')->path($prescription->pdf_path)
        );
    }
}
```

#### Step 3.4: Controller
```php
// app/Http/Controllers/PrescriptionController.php
namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Services\PrescriptionService;

class PrescriptionController extends Controller {
    public function __construct(private PrescriptionService $service) {}

    public function download(Prescription $prescription) {
        $this->authorize('view', $prescription);

        try {
            return $this->service->getPrescriptionPDF($prescription);
        } catch (\Exception $e) {
            return $this->error('Failed to download prescription', 500);
        }
    }

    public function view(Prescription $prescription) {
        $this->authorize('view', $prescription);

        return $this->service->viewPrescriptionPDF($prescription);
    }

    public function list() {
        $prescriptions = Prescription::where('patient_id', auth()->user()->patient->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->success($prescriptions);
    }
}
```

#### Step 3.5: Route
```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/prescriptions', [PrescriptionController::class, 'list']);
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'view']);
    Route::get('/prescriptions/{prescription}/download', [PrescriptionController::class, 'download']);
});
```

#### Step 3.6: PDF Template
```blade
{{-- resources/views/prescriptions/template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Arial', sans-serif; margin: 20px; }
        .header { border-bottom: 3px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #0066cc; }
        .patient-section, .doctor-section, .medicines-section {
            margin-top: 20px;
        }
        .section-title { 
            background: #f0f0f0; 
            padding: 8px; 
            font-weight: bold;
            border-left: 4px solid #0066cc;
        }
        .medicine-item {
            border-left: 2px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            background: #fafafa;
        }
        .footer { 
            margin-top: 40px; 
            border-top: 1px solid #ccc;
            padding-top: 20px;
            text-align: right;
        }
        .watermark {
            opacity: 0.05;
            position: fixed;
            font-size: 80px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ strtoupper($doctor->name) }}</div>

    <div class="header">
        <h1>RESEP OBAT / PRESCRIPTION</h1>
        <p style="margin: 5px 0;">
            <strong>No. Resep:</strong> RX-{{ $prescription->id }}<br/>
            <strong>Tanggal:</strong> {{ $prescription->created_at->format('d/m/Y H:i') }}<br/>
            <strong>Berlaku Sampai:</strong> {{ $prescription->valid_until->format('d/m/Y') }}
        </p>
    </div>

    <div class="patient-section">
        <div class="section-title">DATA PASIEN / PATIENT DATA</div>
        <p>
            <strong>Nama / Name:</strong> {{ $patient->name }}<br/>
            <strong>No. Identitas / ID Number:</strong> {{ $patient->nik }}<br/>
            <strong>Tanggal Lahir / DOB:</strong> {{ $patient->date_of_birth->format('d/m/Y') }}<br/>
            <strong>Usia / Age:</strong> {{ $patient->date_of_birth->diffInYears(now()) }} tahun / years<br/>
            <strong>No. Hp / Phone:</strong> {{ $patient->phone_number }}<br/>
        </p>
    </div>

    <div class="doctor-section">
        <div class="section-title">DOKTER / DOCTOR</div>
        <p>
            <strong>Nama / Name:</strong> {{ $doctor->name }}<br/>
            <strong>SIP:</strong> {{ $doctor->sip_number }}<br/>
            <strong>STR:</strong> {{ $doctor->str_number ?? 'N/A' }}<br/>
            <strong>Spesialis / Specialization:</strong> {{ $doctor->specialization }}<br/>
        </p>
    </div>

    <div class="medicines-section">
        <div class="section-title">OBAT-OBATAN / MEDICINES</div>

        @foreach($prescription->medicines as $index => $medicine)
            <div class="medicine-item">
                <p style="margin: 0;">
                    <strong>{{ $index + 1 }}. {{ $medicine['name'] }}</strong><br/>
                    <strong>Dosis:</strong> {{ $medicine['dosage'] }}<br/>
                    <strong>Frekuensi:</strong> {{ $medicine['frequency'] }}<br/>
                    @if(isset($medicine['notes']))
                        <strong>Catatan:</strong> {{ $medicine['notes'] }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    @if($prescription->instructions)
        <div style="margin-top: 20px; background: #fffacd; padding: 10px; border-left: 4px solid #ff9800;">
            <strong>INSTRUKSI PENTING / IMPORTANT INSTRUCTIONS:</strong><br/>
            {{ $prescription->instructions }}
        </div>
    @endif

    <div class="footer">
        <p style="margin: 0;">
            {{ $doctor->name }}<br/>
            {{ $doctor->sip_number }}<br/>
            Jakarta, {{ now()->format('d/m/Y') }}
        </p>
    </div>
</body>
</html>
```

**Hasil:** ✅ Prescription PDF download working
**Time:** 2-3 jam

---

## 4️⃣ APPOINTMENT REMINDERS (1 hari)

### Masalah Saat Ini
```
- Tidak ada reminder sebelum appointment
- Pasien & dokter bisa lupa
- Tingkat no-show tinggi
```

### Solusi

#### Step 4.1: Create Reminder Job
```bash
php artisan make:job SendAppointmentReminder
php artisan make:notification AppointmentReminderNotification
```

#### Step 4.2: Job Implementation
```php
// app/Jobs/SendAppointmentReminder.php
namespace App\Jobs;

use App\Models\Consultation;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAppointmentReminder implements ShouldQueue {
    use Queueable;

    public function __construct(
        public Consultation $consultation,
        public string $reminderType // '1_day' or '1_hour'
    ) {}

    public function handle() {
        // Send to patient
        $this->consultation->patient->user->notify(
            new AppointmentReminderNotification($this->consultation, $this->reminderType)
        );

        // Send to doctor
        $this->consultation->doctor->user->notify(
            new AppointmentReminderNotification($this->consultation, $this->reminderType)
        );

        // Log reminder sent
        \Log::info("Appointment reminder sent", [
            'consultation_id' => $this->consultation->id,
            'type' => $this->reminderType,
        ]);
    }
}
```

#### Step 4.3: Notification
```php
// app/Notifications/AppointmentReminderNotification.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentReminderNotification extends Notification {
    public function __construct(
        public $consultation,
        public string $reminderType
    ) {}

    public function via($notifiable) {
        return ['mail', 'sms', 'database'];
    }

    public function toMail($notifiable) {
        $message = $this->reminderType === '1_day' 
            ? "You have a consultation scheduled for tomorrow" 
            : "Your consultation is in 1 hour";

        return (new MailMessage())
            ->subject("Appointment Reminder - {$this->consultation->doctor->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line($message)
            ->line("Scheduled: " . $this->consultation->scheduled_time->format('d/m/Y H:i'))
            ->action('View Appointment', route('consultations.show', $this->consultation))
            ->line('Thank you for using our service!');
    }

    public function toSms($notifiable) {
        $time = $this->consultation->scheduled_time->format('d/m/Y H:i');
        $doctor = $this->consultation->doctor->name;

        $reminderText = $this->reminderType === '1_day'
            ? "Reminder: You have a consultation tomorrow at {$time} with Dr. {$doctor}"
            : "Reminder: Your consultation with Dr. {$doctor} starts in 1 hour ({$time})";

        return (new \App\Notifications\Messages\TwilioSmsMessage())
            ->content($reminderText);
    }

    public function toDatabase($notifiable) {
        return [
            'consultation_id' => $this->consultation->id,
            'type' => 'appointment_reminder',
            'reminder_type' => $this->reminderType,
            'message' => "Appointment reminder: {$this->consultation->doctor->name} - {$this->consultation->scheduled_time->format('d/m/Y H:i')}",
            'action_url' => route('consultations.show', $this->consultation),
        ];
    }
}
```

#### Step 4.4: Schedule Reminders
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule) {
    // Reminder 1 day before (at 10:00 AM)
    $schedule->call(function () {
        $consultations = Consultation::where('scheduled_time', '>=', now()->addDay()->startOfDay())
            ->where('scheduled_time', '<', now()->addDay()->endOfDay())
            ->where('status', 'confirmed')
            ->whereDoesntHave('reminders', function($q) {
                $q->where('type', '1_day')->whereDate('sent_at', today());
            })
            ->get();

        foreach ($consultations as $consultation) {
            SendAppointmentReminder::dispatch($consultation, '1_day');
        }
    })->dailyAt('10:00');

    // Reminder 1 hour before
    $schedule->call(function () {
        $consultations = Consultation::where('scheduled_time', '>=', now())
            ->where('scheduled_time', '<=', now()->addHour())
            ->where('status', 'confirmed')
            ->whereDoesntHave('reminders', function($q) {
                $q->where('type', '1_hour')->where('sent_at', '>=', now()->subMinutes(5));
            })
            ->get();

        foreach ($consultations as $consultation) {
            SendAppointmentReminder::dispatch($consultation, '1_hour');
        }
    })->everyMinute();
}
```

#### Step 4.5: Track Reminders
```php
// app/Models/AppointmentReminder.php (new)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentReminder extends Model {
    protected $fillable = ['consultation_id', 'type', 'sent_at'];

    public function consultation() {
        return $this->belongsTo(Consultation::class);
    }
}

// Migration
Schema::create('appointment_reminders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('consultation_id');
    $table->enum('type', ['1_day', '1_hour']);
    $table->timestamp('sent_at')->useCurrent();
    $table->timestamps();
    $table->foreign('consultation_id')->references('id')->on('consultations');
});
```

**Hasil:** ✅ Appointment reminders working
**Time:** 2-3 jam

---

## 5️⃣ ERROR LOGGING DASHBOARD (1 hari)

### Masalah Saat Ini
```
- Tidak ada centralized error tracking
- Errors tidak tercatat di database
- Admin tidak bisa lihat error patterns
```

### Solusi

#### Step 5.1: Create Error Log Model
```bash
php artisan make:model ErrorLog -m
```

```php
// app/Models/ErrorLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model {
    protected $fillable = [
        'user_id', 'exception_type', 'message', 'file', 'line',
        'trace', 'context', 'url', 'method', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'trace' => 'array',
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

// Migration
Schema::create('error_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('exception_type');
    $table->text('message');
    $table->string('file');
    $table->unsignedInteger('line');
    $table->longText('trace')->nullable();
    $table->json('context')->nullable();
    $table->string('url')->nullable();
    $table->string('method')->nullable();
    $table->string('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    $table->index(['exception_type', 'created_at']);
    $table->index('user_id');
});
```

#### Step 5.2: Exception Handler Middleware
```php
// app/Http/Middleware/LogExceptions.php
namespace App\Http\Middleware;

use Closure;
use App\Models\ErrorLog;
use Throwable;

class LogExceptions {
    public function handle($request, Closure $next) {
        try {
            return $next($request);
        } catch (Throwable $e) {
            ErrorLog::create([
                'user_id' => auth()->id(),
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'context' => [
                    'input' => $request->except(['password', 'token']),
                    'headers' => $request->headers->all(),
                ],
            ]);

            throw $e;
        }
    }
}

// Register in app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\LogExceptions::class,
];
```

#### Step 5.3: Admin Dashboard Controller
```php
// app/Http/Controllers/Admin/ErrorLogController.php
namespace App\Http\Controllers\Admin;

use App\Models\ErrorLog;
use Illuminate\Http\Request;

class ErrorLogController extends Controller {
    public function index(Request $request) {
        $this->authorize('admin');

        $errors = ErrorLog::query();

        // Filter by exception type
        if ($request->exception_type) {
            $errors->where('exception_type', $request->exception_type);
        }

        // Filter by date range
        if ($request->from_date) {
            $errors->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $errors->whereDate('created_at', '<=', $request->to_date);
        }

        $errors = $errors->latest()->paginate(20);

        // Get stats
        $stats = [
            'total_errors' => ErrorLog::count(),
            'today_errors' => ErrorLog::whereDate('created_at', today())->count(),
            'top_errors' => ErrorLog::selectRaw('exception_type, COUNT(*) as count')
                ->groupBy('exception_type')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'errors_by_day' => ErrorLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->get(),
        ];

        return view('admin.error-logs.index', [
            'errors' => $errors,
            'stats' => $stats,
            'exceptionTypes' => ErrorLog::distinct('exception_type')->pluck('exception_type'),
        ]);
    }

    public function show(ErrorLog $errorLog) {
        $this->authorize('admin');
        return view('admin.error-logs.show', ['error' => $errorLog]);
    }

    public function delete(ErrorLog $errorLog) {
        $this->authorize('admin');
        $errorLog->delete();
        return back()->with('success', 'Error log deleted');
    }

    public function clearOld(Request $request) {
        $this->authorize('admin');
        $daysOld = $request->days ?? 30;
        
        $count = ErrorLog::where('created_at', '<', now()->subDays($daysOld))->delete();
        
        return back()->with('success', "Deleted {$count} error logs older than {$daysOld} days");
    }
}
```

#### Step 5.4: Routes
```php
// routes/admin.php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function() {
    Route::get('/error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
    Route::get('/error-logs/{errorLog}', [ErrorLogController::class, 'show'])->name('error-logs.show');
    Route::delete('/error-logs/{errorLog}', [ErrorLogController::class, 'delete'])->name('error-logs.delete');
    Route::post('/error-logs/clear-old', [ErrorLogController::class, 'clearOld'])->name('error-logs.clear-old');
});
```

#### Step 5.5: Dashboard View
```blade
{{-- resources/views/admin/error-logs/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Error Log Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Total Errors</h5>
                    <h2>{{ $stats['total_errors'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Today's Errors</h5>
                    <h2>{{ $stats['today_errors'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h3>Top 5 Errors</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Exception Type</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['top_errors'] as $error)
                        <tr>
                            <td>{{ $error->exception_type }}</td>
                            <td><span class="badge badge-danger">{{ $error->count }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <h3>Recent Errors</h3>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Message</th>
                    <th>User</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($errors as $error)
                    <tr>
                        <td><span class="badge badge-danger">{{ class_basename($error->exception_type) }}</span></td>
                        <td>{{ Str::limit($error->message, 50) }}</td>
                        <td>{{ $error->user?->name ?? 'Guest' }}</td>
                        <td>{{ $error->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('error-logs.show', $error) }}" class="btn btn-sm btn-info">View</a>
                            <form method="POST" action="{{ route('error-logs.delete', $error) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $errors->links() }}
    </div>
</div>
@endsection
```

**Hasil:** ✅ Error logging dashboard working
**Time:** 3 jam

---

## 📊 SUMMARY - QUICK WINS

| Feature | Time | Difficulty | Impact |
|---------|------|-----------|--------|
| API Documentation | 3h | Easy | High |
| SMS Notifications | 4h | Easy | High |
| Prescription PDF | 3h | Easy | High |
| Appointment Reminders | 3h | Medium | High |
| Error Logging Dashboard | 3h | Medium | Medium |
| **TOTAL** | **16h** | **Easy-Medium** | **Excellent** |

---

## 🚀 EXECUTION PLAN

```
Day 1 (3 hours):
- API Documentation + Swagger
- SMS Notification System

Day 2 (3 hours):
- Prescription PDF Download
- Testing & Integration

Day 3 (4 hours):
- Appointment Reminders
- Scheduled Jobs

Day 4 (3 hours):
- Error Logging Dashboard
- Testing & Deployment

Day 5:
- Bug fixes
- Documentation update
- Prepare for demo
```

---

**Next: Mulai dari Quick Win #1 atau ada yang ingin prioritas lebih tinggi?**
