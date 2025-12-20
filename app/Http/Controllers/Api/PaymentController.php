<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TaxRecord;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ============================================
 * PAYMENT CONTROLLER
 * ============================================
 * 
 * Mengelola payment transactions & invoicing
 * 
 * Endpoints:
 * POST   /api/v1/payments - Create payment
 * GET    /api/v1/payments/{id} - Get payment details
 * POST   /api/v1/payments/{id}/confirm - Confirm payment
 * POST   /api/v1/payments/{id}/refund - Refund payment
 * GET    /api/v1/invoices/{id} - Get invoice
 * GET    /api/v1/invoices/{id}/download - Download invoice PDF
 * GET    /api/v1/payments/history - Payment history
 * POST   /api/v1/webhooks/payment - Stripe webhook
 */
class PaymentController extends Controller
{
    /**
     * Create new payment for consultation
     * POST /api/v1/payments
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:konsultasis,id',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:stripe,gcash,bank_transfer,e_wallet',
        ]);

        $user = Auth::user();
        $consultation = Konsultasi::find($validated['consultation_id']);

        // Verify consultation ownership (patient only)
        if ($consultation->pasien_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Create payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'consultation_id' => $consultation->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_PENDING,
            ]);

            // Calculate and add taxes
            $this->calculateTaxes($payment);

            // Create invoice
            $invoice = $this->generateInvoice($payment);

            DB::commit();

            return response()->json([
                'data' => [
                    'payment' => $payment,
                    'invoice' => $invoice,
                ],
                'message' => 'Pembayaran berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat pembayaran',
                'error' => $e->getMessage(),
            ], 500);
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
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        // Authorization: can only view own payments
        if ($payment->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => [
                'payment' => $payment->load('invoices', 'taxRecords'),
                'status_label' => $payment->getStatusLabel(),
                'method_label' => $payment->getMethodLabel(),
                'formatted_amount' => $payment->getFormattedAmount(),
            ],
        ]);
    }

    /**
     * Confirm payment (after Stripe/GCash callback)
     * POST /api/v1/payments/{id}/confirm
     */
    public function confirm(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'receipt_url' => 'nullable|url',
        ]);

        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        if (!$payment->isPending()) {
            return response()->json([
                'message' => 'Status pembayaran tidak valid untuk confirm',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Mark payment as completed
            $payment->markCompleted(
                $validated['transaction_id'],
                $validated['receipt_url'] ?? null
            );

            // Mark invoice as paid
            $invoice = $payment->invoices()->first();
            if ($invoice) {
                $invoice->markPaid();
            }

            // Mark taxes as calculated (if not already)
            $payment->taxRecords()->update(['status' => TaxRecord::STATUS_CALCULATED]);

            DB::commit();

            return response()->json([
                'data' => [
                    'payment' => $payment->refresh(),
                    'invoice' => $invoice,
                ],
                'message' => 'Pembayaran berhasil dikonfirmasi',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengkonfirmasi pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refund payment
     * POST /api/v1/payments/{id}/refund
     */
    public function refund(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:1000',
            'reason' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        // Only admin or payment owner can refund
        if ($payment->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$payment->isCompleted()) {
            return response()->json([
                'message' => 'Hanya pembayaran yang selesai yang bisa di-refund',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $refundAmount = $validated['amount'] ?? $payment->amount;

            if ($refundAmount > $payment->amount) {
                return response()->json([
                    'message' => 'Jumlah refund tidak boleh lebih dari pembayaran asli',
                ], 400);
            }

            // Process refund
            $payment->refund($refundAmount);
            $payment->notes = "Refund reason: " . $validated['reason'];
            $payment->save();

            DB::commit();

            return response()->json([
                'data' => [
                    'payment' => $payment,
                ],
                'message' => 'Refund berhasil diproses',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history
     * GET /api/v1/payments/history
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $payments = Payment::byUser($user->id)
            ->with(['invoices', 'consultation'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->getFormattedAmount(),
                    'status' => $payment->getStatusLabel(),
                    'method' => $payment->getMethodLabel(),
                    'invoice_number' => $payment->invoices()->first()?->invoice_number,
                    'created_at' => $payment->created_at->toDateString(),
                ];
            }),
            'links' => $payments->links(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Stripe webhook callback
     * POST /api/v1/webhooks/payment
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature dari Stripe
        $signature = $request->header('Stripe-Signature');
        // TODO: Implement Stripe signature verification

        $event = json_decode($request->getContent(), true);

        if ($event['type'] === 'payment_intent.succeeded') {
            $paymentIntentId = $event['data']['object']['id'];
            
            $payment = Payment::where('transaction_id', $paymentIntentId)->first();
            if ($payment) {
                $payment->markCompleted($paymentIntentId);
                
                // Mark invoice as paid
                $invoice = $payment->invoices()->first();
                if ($invoice) {
                    $invoice->markPaid();
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate taxes for payment
     */
    private function calculateTaxes(Payment $payment)
    {
        // PPN (VAT) - 11% standard rate
        $ppnAmount = TaxRecord::calculate($payment->amount, TaxRecord::TYPE_PPN);
        TaxRecord::create([
            'payment_id' => $payment->id,
            'tax_type' => TaxRecord::TYPE_PPN,
            'tax_rate' => TaxRecord::RATES[TaxRecord::TYPE_PPN],
            'base_amount' => $payment->amount,
            'tax_amount' => $ppnAmount,
            'status' => TaxRecord::STATUS_CALCULATED,
        ]);

        // PPh (Personal Income Tax) - 15% untuk professional services
        $pphAmount = TaxRecord::calculate($payment->amount, TaxRecord::TYPE_PPH);
        TaxRecord::create([
            'payment_id' => $payment->id,
            'tax_type' => TaxRecord::TYPE_PPH,
            'tax_rate' => TaxRecord::RATES[TaxRecord::TYPE_PPH],
            'base_amount' => $payment->amount,
            'tax_amount' => $pphAmount,
            'status' => TaxRecord::STATUS_CALCULATED,
        ]);
    }

    /**
     * Generate invoice from payment
     */
    private function generateInvoice(Payment $payment): Invoice
    {
        $totalTax = $payment->taxRecords()->sum('tax_amount');
        $total = $payment->amount + $totalTax;

        $invoice = Invoice::create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'invoice_number' => Invoice::generateNumber(),
            'invoice_date' => today(),
            'due_date' => today()->addDays(7),
            'subtotal' => $payment->amount,
            'tax_amount' => $totalTax,
            'discount_amount' => 0,
            'total_amount' => $total,
            'status' => Invoice::STATUS_DRAFT,
        ]);

        // Add invoice items
        if ($payment->consultation_id) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Konsultasi Medis',
                'item_type' => InvoiceItem::TYPE_CONSULTATION,
                'quantity' => 1,
                'unit_price' => $payment->amount,
                'amount' => $payment->amount,
            ]);
        }

        if ($payment->emergency_id) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Biaya Penanganan Darurat',
                'item_type' => InvoiceItem::TYPE_EMERGENCY,
                'quantity' => 1,
                'unit_price' => 500000, // Fixed emergency fee
                'amount' => 500000,
            ]);
        }

        return $invoice;
    }
}
