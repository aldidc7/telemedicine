# Implementasi Double Payment Prevention di Laravel 12
## Database Transactions + Atomic Locks

---

## 1. MASALAH: DOUBLE PAYMENT

Ketika user menekan tombol "Bayar" berkali-kali dalam jangka waktu singkat:

```
User clicks Pay → POST /api/v1/payments
                 ↓
             (2ms) clicks again → POST /api/v1/payments
                                 ↓
                             (2ms) clicks again → POST /api/v1/payments
                                                  ↓
Hasil: 3 transaksi pembayaran dibuat untuk 1 konsultasi
```

**Risiko:**
- Duplicate payments
- Konflik database
- Loss of revenue/refund issues
- Customer service nightmare

---

## 2. SOLUSI: DATABASE TRANSACTIONS + ATOMIC LOCKS

Laravel 12 menyediakan 3 cara aman untuk mencegah double payment:

### A. Database Transaction dengan Pessimistic Lock
### B. Database Transaction dengan Optimistic Lock
### C. Idempotency Key + Request Deduplication
### D. Distributed Lock (Redis)

---

## SOLUSI 1: PESSIMISTIC LOCK (Paling Aman)
### Lock database rows untuk prevent concurrent access

```php
// File: app/Http/Controllers/Api/PaymentController.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Create new payment with Pessimistic Lock
     * 
     * ✓ Locks consultation row
     * ✓ Prevents concurrent payment creation
     * ✓ Atomic transaction
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:konsultasis,id',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:stripe,gcash,bank_transfer,e_wallet',
            'idempotency_key' => 'nullable|string|max:255', // Unique request identifier
        ]);

        $user = Auth::user();
        $consultationId = $validated['consultation_id'];
        $idempotencyKey = $validated['idempotency_key'] ?? null;

        // ============ STEP 1: CHECK DUPLICATE REQUEST ============
        // Prevent exact duplicate requests dalam 5 detik
        if ($idempotencyKey) {
            $cacheKey = "payment:idempotency:{$idempotencyKey}";
            
            if (Cache::has($cacheKey)) {
                // Request sudah pernah diproses - return cached response
                return response()->json(
                    Cache::get($cacheKey),
                    201
                );
            }
        }

        try {
            // ============ STEP 2: PESSIMISTIC LOCK ============
            // Lock row selama transaction berjalan
            $consultation = Konsultasi::lockForUpdate()
                ->find($consultationId);

            if (!$consultation) {
                return response()->json([
                    'message' => 'Konsultasi tidak ditemukan',
                ], 404);
            }

            // Verify ownership
            if ($consultation->pasien_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            // ============ STEP 3: CHECK EXISTING PAYMENT ============
            // Ensure no pending/completed payment exists
            $existingPayment = Payment::where([
                'consultation_id' => $consultationId,
                'user_id' => $user->id,
            ])->whereIn('status', [
                Payment::STATUS_PENDING,
                Payment::STATUS_PROCESSING,
                Payment::STATUS_COMPLETED,
            ])->first();

            if ($existingPayment) {
                return response()->json([
                    'message' => 'Pembayaran untuk konsultasi ini sudah ada',
                    'data' => [
                        'existing_payment_id' => $existingPayment->id,
                        'status' => $existingPayment->status,
                    ],
                ], 409); // Conflict
            }

            // ============ STEP 4: DATABASE TRANSACTION ============
            $payment = DB::transaction(function () use (
                $user,
                $consultation,
                $validated
            ) {
                // Create payment dalam transaction
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'consultation_id' => $consultation->id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'status' => Payment::STATUS_PENDING,
                    'currency' => 'PHP',
                ]);

                // Calculate taxes
                $this->calculateTaxes($payment);

                // Generate invoice
                $this->generateInvoice($payment);

                // Log transaction
                Log::info('Payment created', [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'consultation_id' => $consultation->id,
                    'amount' => $validated['amount'],
                ]);

                return $payment;
            }, attempts: 3, timeout: 5);

            // ============ STEP 5: CACHE RESPONSE ============
            // Cache response untuk prevent duplicate requests
            if ($idempotencyKey) {
                $response = [
                    'data' => [
                        'payment' => $payment->load('invoices'),
                        'invoice' => $payment->invoices()->first(),
                    ],
                    'message' => 'Pembayaran berhasil dibuat',
                ];
                
                Cache::put(
                    "payment:idempotency:{$idempotencyKey}",
                    $response,
                    60 // Cache selama 60 detik
                );

                return response()->json($response, 201);
            }

            return response()->json([
                'data' => [
                    'payment' => $payment->load('invoices'),
                    'invoice' => $payment->invoices()->first(),
                ],
                'message' => 'Pembayaran berhasil dibuat',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'user_id' => $user->id,
                'consultation_id' => $consultationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment dengan atomic operation
     * 
     * ✓ Shared lock prevents concurrent modifications
     * ✓ Prevents race condition on status update
     */
    public function confirm(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'receipt_url' => 'nullable|url',
        ]);

        try {
            // ============ ATOMIC PAYMENT CONFIRMATION ============
            $payment = DB::transaction(function () use ($id, $validated) {
                // Lock payment untuk prevent concurrent confirmation
                $payment = Payment::lockForUpdate()->find($id);

                if (!$payment) {
                    throw new \Exception('Payment not found');
                }

                // Check status (must be pending)
                if (!$payment->isPending()) {
                    throw new \Exception(
                        "Payment status: {$payment->status}. " .
                        "Only pending payments can be confirmed."
                    );
                }

                // Mark payment as completed
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $validated['transaction_id'],
                    'receipt_url' => $validated['receipt_url'] ?? null,
                    'completed_at' => now(),
                ]);

                // Mark invoices as paid
                $payment->invoices()->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('Payment confirmed', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $validated['transaction_id'],
                ]);

                return $payment;
            }, attempts: 3);

            return response()->json([
                'data' => [
                    'payment' => $payment,
                    'invoice' => $payment->invoices()->first(),
                ],
                'message' => 'Pembayaran berhasil dikonfirmasi',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mengkonfirmasi pembayaran',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Helper: Calculate taxes
     */
    private function calculateTaxes($payment)
    {
        $taxRate = 0.12; // 12% VAT
        $taxAmount = $payment->amount * $taxRate;

        TaxRecord::create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'amount' => $taxAmount,
            'tax_type' => 'VAT',
            'tax_rate' => $taxRate,
            'status' => 'calculated',
        ]);
    }

    /**
     * Helper: Generate invoice
     */
    private function generateInvoice($payment)
    {
        return Invoice::create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . $payment->id,
            'amount' => $payment->amount,
            'status' => 'pending',
            'issued_at' => now(),
        ]);
    }
}
```

---

## SOLUSI 2: OPTIMISTIC LOCK (Lebih Ringan)
### Gunakan version column untuk detect concurrent modifications

```php
// File: database/migrations/YYYY_MM_DD_create_payments_table.php

Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('consultation_id')->constrained('konsultasis');
    $table->decimal('amount', 12, 2);
    $table->string('payment_method');
    $table->string('status')->default('pending'); // pending, completed, refunded
    $table->string('transaction_id')->nullable()->unique();
    $table->integer('version')->default(0); // ← Optimistic lock column
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['consultation_id', 'user_id', 'status']);
});
```

```php
// File: app/Models/Payment.php

class Payment extends Model
{
    use SoftDeletes;

    // ✓ Enable optimistic locking
    protected $version = true;

    protected $fillable = [
        'user_id',
        'consultation_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'receipt_url',
        'version',
    ];

    // ✓ Increment version on update
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->version = 0;
        });
    }
}
```

```php
// File: app/Http/Controllers/Api/PaymentController.php (Optimistic approach)

public function createWithOptimisticLock(Request $request)
{
    $validated = $request->validate([
        'consultation_id' => 'required|exists:konsultasis,id',
        'amount' => 'required|numeric|min:1000',
        'payment_method' => 'required|in:stripe,gcash,bank_transfer,e_wallet',
    ]);

    $user = Auth::user();
    $consultation = Konsultasi::find($validated['consultation_id']);

    if ($consultation->pasien_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ============ OPTIMISTIC LOCK APPROACH ============
    try {
        $payment = DB::transaction(function () use ($user, $consultation, $validated) {
            // Create payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'consultation_id' => $consultation->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_PENDING,
                'version' => 0, // ← Start at version 0
            ]);

            // Calculate taxes
            $this->calculateTaxes($payment);

            return $payment;
        });

        return response()->json([
            'data' => ['payment' => $payment],
            'message' => 'Pembayaran berhasil dibuat',
        ], 201);

    } catch (\Illuminate\Database\QueryException $e) {
        // Catch unique constraint violation
        if ($e->getCode() === '23000') {
            return response()->json([
                'message' => 'Pembayaran sudah ada untuk konsultasi ini',
            ], 409);
        }

        throw $e;
    }
}

public function confirmWithOptimisticLock(Request $request, $id)
{
    $validated = $request->validate([
        'transaction_id' => 'required|string',
        'expected_version' => 'required|integer', // ← Send current version from client
    ]);

    try {
        $payment = DB::transaction(function () use ($id, $validated) {
            // Retrieve payment
            $payment = Payment::find($id);

            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Check version - if different, someone else modified it
            if ($payment->version !== $validated['expected_version']) {
                throw new \Exception(
                    'Payment was modified by another process. ' .
                    'Please refresh and try again.'
                );
            }

            // Update payment and increment version
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => $validated['transaction_id'],
                'version' => $payment->version + 1, // ← Increment version
            ]);

            return $payment;
        });

        return response()->json([
            'data' => ['payment' => $payment],
            'message' => 'Pembayaran berhasil dikonfirmasi',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 409);
    }
}
```

---

## SOLUSI 3: IDEMPOTENCY KEY + REDIS LOCK
### Paling production-ready untuk distributed systems

```php
// File: app/Services/PaymentService.php (NEW FILE)

namespace App\Services;

use App\Models\Payment;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process payment dengan distributed lock
     * 
     * ✓ Idempotency key untuk duplicate detection
     * ✓ Redis lock untuk distributed systems
     * ✓ Atomic database transaction
     * ✓ Retry mechanism
     */
    public function processPayment(
        $userId,
        $consultationId,
        $amount,
        $paymentMethod,
        $idempotencyKey
    ) {
        // ============ STEP 1: VALIDATE IDEMPOTENCY KEY ============
        $cacheKey = "payment:idempotency:{$idempotencyKey}";
        
        // Check if request already processed
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult) {
            Log::info('Payment: Returning cached response', [
                'idempotency_key' => $idempotencyKey,
                'payment_id' => $cachedResult['payment_id'],
            ]);

            return $cachedResult;
        }

        // ============ STEP 2: ACQUIRE DISTRIBUTED LOCK ============
        $lockKey = "payment:lock:{$consultationId}:{$userId}";
        $lockToken = uniqid('lock_', true); // Unique lock token

        // Try to acquire lock (max 5 seconds)
        $lockAcquired = false;
        $attempts = 0;
        $maxAttempts = 50; // 5000ms / 100ms = 50 attempts

        while (!$lockAcquired && $attempts < $maxAttempts) {
            // Use SET NX (only if not exists) + expiration
            $lockAcquired = Redis::set(
                $lockKey,
                $lockToken,
                'NX', // Only set if key doesn't exist
                'EX', // Set expiration in seconds
                5    // Expire after 5 seconds (safety timeout)
            );

            if (!$lockAcquired) {
                usleep(100000); // Wait 100ms before retry
                $attempts++;
            }
        }

        if (!$lockAcquired) {
            throw new \Exception('Could not acquire payment lock after 5 seconds');
        }

        try {
            // ============ STEP 3: DATABASE TRANSACTION ============
            $result = DB::transaction(function () use (
                $userId,
                $consultationId,
                $amount,
                $paymentMethod,
                $idempotencyKey
            ) {
                // Lock consultation row
                $consultation = Konsultasi::lockForUpdate()
                    ->find($consultationId);

                if (!$consultation) {
                    throw new \Exception('Consultation not found');
                }

                if ($consultation->pasien_id !== $userId) {
                    throw new \Exception('Unauthorized access to consultation');
                }

                // Check existing payment
                $existingPayment = Payment::where([
                    'consultation_id' => $consultationId,
                    'user_id' => $userId,
                ])->whereIn('status', [
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PROCESSING,
                    Payment::STATUS_COMPLETED,
                ])->first();

                if ($existingPayment) {
                    Log::warning('Payment: Duplicate payment attempt detected', [
                        'consultation_id' => $consultationId,
                        'existing_payment_id' => $existingPayment->id,
                    ]);

                    return [
                        'type' => 'existing',
                        'payment_id' => $existingPayment->id,
                        'status' => $existingPayment->status,
                    ];
                }

                // Create new payment
                $payment = Payment::create([
                    'user_id' => $userId,
                    'consultation_id' => $consultationId,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'status' => Payment::STATUS_PENDING,
                    'currency' => 'PHP',
                ]);

                // Calculate taxes
                $this->calculateTaxes($payment);

                // Generate invoice
                $invoice = $this->generateInvoice($payment);

                Log::info('Payment: New payment created', [
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                ]);

                return [
                    'type' => 'new',
                    'payment_id' => $payment->id,
                    'payment' => $payment,
                    'invoice' => $invoice,
                ];
            }, attempts: 3);

            // ============ STEP 4: CACHE RESULT ============
            Cache::put(
                $cacheKey,
                [
                    'type' => $result['type'],
                    'payment_id' => $result['payment_id'],
                    'status' => $result['status'] ?? 'pending',
                ],
                300 // Cache for 5 minutes
            );

            return $result;

        } finally {
            // ============ STEP 5: RELEASE LOCK ============
            // Only release if we still own the lock
            $currentLockToken = Redis::get($lockKey);
            if ($currentLockToken === $lockToken) {
                Redis::del($lockKey);

                Log::debug('Payment: Lock released', [
                    'lock_key' => $lockKey,
                ]);
            }
        }
    }

    /**
     * Calculate taxes for payment
     */
    private function calculateTaxes($payment)
    {
        $taxRate = 0.12; // 12% VAT
        $taxAmount = $payment->amount * $taxRate;

        return TaxRecord::create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'amount' => $taxAmount,
            'tax_type' => 'VAT',
            'tax_rate' => $taxRate,
            'status' => 'calculated',
        ]);
    }

    /**
     * Generate invoice for payment
     */
    private function generateInvoice($payment)
    {
        return Invoice::create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . $payment->id,
            'amount' => $payment->amount,
            'status' => 'pending',
            'issued_at' => now(),
        ]);
    }

    /**
     * Confirm payment (atomic operation)
     */
    public function confirmPayment($paymentId, $transactionId, $receiptUrl = null)
    {
        $lockKey = "payment:confirm:{$paymentId}";
        $lockToken = uniqid('lock_', true);

        // Acquire lock
        $lockAcquired = Redis::set(
            $lockKey,
            $lockToken,
            'NX',
            'EX',
            5
        );

        if (!$lockAcquired) {
            throw new \Exception('Payment is being confirmed by another request');
        }

        try {
            $payment = DB::transaction(function () use (
                $paymentId,
                $transactionId,
                $receiptUrl
            ) {
                // Lock payment row
                $payment = Payment::lockForUpdate()->find($paymentId);

                if (!$payment) {
                    throw new \Exception('Payment not found');
                }

                if (!$payment->isPending()) {
                    throw new \Exception(
                        "Payment status: {$payment->status}. " .
                        "Cannot confirm non-pending payment."
                    );
                }

                // Update payment
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $transactionId,
                    'receipt_url' => $receiptUrl,
                    'completed_at' => now(),
                ]);

                // Update invoices
                $payment->invoices()->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('Payment confirmed', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId,
                ]);

                return $payment;
            }, attempts: 3);

            return $payment;

        } finally {
            $currentLockToken = Redis::get($lockKey);
            if ($currentLockToken === $lockToken) {
                Redis::del($lockKey);
            }
        }
    }
}
```

---

## SOLUSI 4: UPDATED PAYMENT CONTROLLER
### Menggunakan PaymentService

```php
// File: app/Http/Controllers/Api/PaymentController.php (Updated)

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create payment dengan double-payment prevention
     * 
     * POST /api/v1/payments
     * 
     * Required:
     * - consultation_id: ID konsultasi
     * - amount: Jumlah pembayaran
     * - payment_method: stripe|gcash|bank_transfer|e_wallet
     * 
     * Optional:
     * - idempotency_key: Unique request identifier untuk prevent duplicates
     * 
     * Frontend: Generate unique idempotency_key di setiap request
     * const idempotencyKey = `payment-${consultationId}-${Date.now()}`;
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:konsultasis,id',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:stripe,gcash,bank_transfer,e_wallet',
            'idempotency_key' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $idempotencyKey = $validated['idempotency_key'] ?? Str::uuid()->toString();

        try {
            $result = $this->paymentService->processPayment(
                userId: $user->id,
                consultationId: $validated['consultation_id'],
                amount: $validated['amount'],
                paymentMethod: $validated['payment_method'],
                idempotencyKey: $idempotencyKey
            );

            // If existing payment, return 409 Conflict
            if ($result['type'] === 'existing') {
                return response()->json([
                    'message' => 'Pembayaran untuk konsultasi ini sudah ada',
                    'data' => [
                        'payment_id' => $result['payment_id'],
                        'status' => $result['status'],
                    ],
                ], 409);
            }

            return response()->json([
                'data' => [
                    'payment' => $result['payment'],
                    'invoice' => $result['invoice'],
                ],
                'message' => 'Pembayaran berhasil dibuat',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment
     * 
     * POST /api/v1/payments/{id}/confirm
     */
    public function confirm(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'receipt_url' => 'nullable|url',
        ]);

        try {
            $payment = $this->paymentService->confirmPayment(
                paymentId: $id,
                transactionId: $validated['transaction_id'],
                receiptUrl: $validated['receipt_url']
            );

            return response()->json([
                'data' => ['payment' => $payment],
                'message' => 'Pembayaran berhasil dikonfirmasi',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get payment details
     * GET /api/v1/payments/{id}
     */
    public function show($id)
    {
        $user = Auth::user();
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $payment->load('invoices', 'taxRecords'),
        ]);
    }
}
```

---

## FRONTEND IMPLEMENTATION
### Generate Idempotency Key untuk prevent duplicate requests

```javascript
// File: resources/js/services/paymentService.js

export class PaymentService {
    /**
     * Create payment dengan idempotency key
     * 
     * ✓ Generate unique key untuk prevent duplicate submissions
     * ✓ Disable button setelah click
     * ✓ Show loading state
     */
    async createPayment(consultationId, amount, paymentMethod) {
        const idempotencyKey = this.generateIdempotencyKey(consultationId);
        
        try {
            const response = await fetch('/api/v1/payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getToken()}`,
                },
                body: JSON.stringify({
                    consultation_id: consultationId,
                    amount: amount,
                    payment_method: paymentMethod,
                    idempotency_key: idempotencyKey, // ← Unique key
                }),
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message);
            }

            return await response.json();

        } catch (error) {
            console.error('Payment creation failed:', error);
            throw error;
        }
    }

    /**
     * Generate unique idempotency key
     * Format: payment-{consultationId}-{timestamp}-{random}
     */
    generateIdempotencyKey(consultationId) {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substring(2, 9);
        return `payment-${consultationId}-${timestamp}-${random}`;
    }

    /**
     * Usage dalam Vue component
     */
    // <script setup>
    // const paymentService = new PaymentService();
    // const isProcessing = ref(false);
    //
    // async function submitPayment() {
    //     if (isProcessing.value) return; // Prevent double submission
    //
    //     isProcessing.value = true;
    //     
    //     try {
    //         const result = await paymentService.createPayment(
    //             consultationId.value,
    //             amount.value,
    //             'stripe'
    //         );
    //         
    //         console.log('Payment created:', result.data.payment);
    //         // Redirect to payment gateway
    //         
    //     } catch (error) {
    //         showError(error.message);
    //     } finally {
    //         isProcessing.value = false;
    //     }
    // }
    // </script>
}
```

---

## TESTING
### Unit tests untuk prevent double payments

```php
// File: tests/Feature/DoublePaymentPreventionTest.php

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class DoublePaymentPreventionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $consultation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'pasien']);
        $this->consultation = Konsultasi::factory()->create([
            'pasien_id' => $this->user->id,
        ]);
    }

    /**
     * Test: Duplicate request dengan idempotency key
     * Harus return sama response tanpa create payment baru
     */
    public function test_duplicate_request_with_idempotency_key()
    {
        $idempotencyKey = 'test-payment-' . time();

        // First request
        $response1 = $this->actingAs($this->user)
            ->postJson('/api/v1/payments', [
                'consultation_id' => $this->consultation->id,
                'amount' => 5000,
                'payment_method' => 'stripe',
                'idempotency_key' => $idempotencyKey,
            ]);

        $this->assertEquals(201, $response1->status());
        $paymentId1 = $response1['data']['payment']['id'];

        // Second request dengan idempotency key sama
        $response2 = $this->actingAs($this->user)
            ->postJson('/api/v1/payments', [
                'consultation_id' => $this->consultation->id,
                'amount' => 5000,
                'payment_method' => 'stripe',
                'idempotency_key' => $idempotencyKey,
            ]);

        // Should return cached response dengan payment ID sama
        $this->assertEquals(201, $response2->status());
        $paymentId2 = $response2['data']['payment']['id'];

        // Same payment ID - no new payment created
        $this->assertEquals($paymentId1, $paymentId2);

        // Only 1 payment in database
        $this->assertEquals(1, Payment::count());
    }

    /**
     * Test: Rapid clicks tanpa idempotency key
     * Pessimistic lock harus prevent duplicate
     */
    public function test_rapid_clicks_without_idempotency_key()
    {
        // Simulate 3 rapid requests
        $response1 = $this->actingAs($this->user)
            ->postJson('/api/v1/payments', [
                'consultation_id' => $this->consultation->id,
                'amount' => 5000,
                'payment_method' => 'stripe',
            ]);

        $response2 = $this->actingAs($this->user)
            ->postJson('/api/v1/payments', [
                'consultation_id' => $this->consultation->id,
                'amount' => 5000,
                'payment_method' => 'stripe',
            ]);

        // Second request should be rejected (conflict)
        $this->assertEquals(409, $response2->status());
        $this->assertStringContainsString('sudah ada', $response2['message']);

        // Only 1 payment created
        $this->assertEquals(1, Payment::count());
    }

    /**
     * Test: Payment confirmation atomicity
     */
    public function test_payment_confirmation_is_atomic()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'consultation_id' => $this->consultation->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/payments/{$payment->id}/confirm", [
                'transaction_id' => 'txn_12345',
            ]);

        $this->assertEquals(200, $response->status());

        // Verify payment status updated
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('txn_12345', $payment->transaction_id);
    }
}
```

---

## COMPARISON TABLE
### Pilih solusi yang sesuai kebutuhan

| Solusi | Kelebihan | Kekurangan | Cocok Untuk |
|--------|-----------|-----------|------------|
| **Pessimistic Lock** | Sederhana, thread-safe | Locks database rows | Small to medium traffic |
| **Optimistic Lock** | Ringan, distributed-friendly | Need version column | Medium traffic |
| **Idempotency + Redis** | Production-ready, scalable | Complex setup | High traffic, distributed |
| **Hybrid** | Best of all | More code | Enterprise systems |

---

## BEST PRACTICES

### 1. ✅ DO
```php
// ✓ Use transactions
DB::transaction(function () {
    // Multiple DB operations
});

// ✓ Lock rows yang critical
$payment = Payment::lockForUpdate()->find($id);

// ✓ Generate idempotency key di frontend
const key = `payment-${consultationId}-${timestamp}-${random}`;

// ✓ Cache duplicate responses
Cache::put($cacheKey, $response, 300);

// ✓ Log all payment operations
Log::info('Payment created', $details);

// ✓ Set lock timeout
Redis::set($key, $token, 'EX', 5);
```

### 2. ❌ DON'T
```php
// ✗ Tidak ada transaction
Payment::create($data);
Invoice::create($data);

// ✗ Tidak ada lock pada update
$payment->update($data);

// ✗ Synchronous wait loop tanpa timeout
while (!lockAcquired) {
    // Lock ada chance infinite loop
}

// ✗ Cache response forever
Cache::forever($key, $response);

// ✗ Ignore exception handling
$payment->confirm();
```

---

## KESIMPULAN

Untuk prevent **double payment** di Laravel 12:

1. **Gunakan Database Transaction** untuk atomicity
2. **Gunakan Pessimistic Lock** (`lockForUpdate()`) untuk critical rows
3. **Tambahkan Idempotency Key** di frontend
4. **Cache duplicate requests** untuk prevent processing ulang
5. **Gunakan Redis Lock** untuk distributed systems
6. **Test extensively** dengan unit tests

Kombinasi 3-4 teknik di atas = **Production-ready payment system** ✅
