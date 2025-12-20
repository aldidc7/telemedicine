<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Payment Model
 * 
 * Represents payment transactions dalam system
 * Tracks: consultation fees, emergency charges, refunds
 * 
 * Methods:
 * - isPending(): Check jika payment masih pending
 * - isCompleted(): Check jika payment selesai
 * - isFailed(): Check jika payment gagal
 * - refund(): Proses refund request
 * 
 * Relationships:
 * - user: User yang melakukan pembayaran
 * - consultation: Konsultasi terkait
 * - emergency: Emergency case (jika ada)
 * - invoices: Invoice yang dibuat dari payment
 * - taxRecords: Tax records untuk compliance
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'consultation_id',
        'emergency_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'transaction_id',
        'receipt_url',
        'gateway_response',
        'failed_reason',
        'refunded_at',
        'refund_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'gateway_response' => 'json',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status enum: pending, processing, completed, failed, refunded
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment methods: stripe, gcash, bank_transfer, e_wallet
     */
    const METHOD_STRIPE = 'stripe';
    const METHOD_GCASH = 'gcash';
    const METHOD_BANK = 'bank_transfer';
    const METHOD_WALLET = 'e_wallet';

    // ==================== RELATIONSHIPS ====================

    /**
     * User yang melakukan pembayaran
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Konsultasi terkait
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class);
    }

    /**
     * Emergency case (jika ada)
     */
    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class)->nullable();
    }

    /**
     * Invoice yang dibuat dari payment
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Tax records untuk compliance
     */
    public function taxRecords(): HasMany
    {
        return $this->hasMany(TaxRecord::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Filter pembayaran yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filter pembayaran yang selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Filter pembayaran yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Filter pembayaran dengan refund
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter by payment method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Filter payments dalam date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== METHODS ====================

    /**
     * Check jika payment masih pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check jika payment selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check jika payment gagal
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check jika sudah di-refund
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted($transactionId = null, $receiptUrl = null): self
    {
        $this->status = self::STATUS_COMPLETED;
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        if ($receiptUrl) {
            $this->receipt_url = $receiptUrl;
        }
        $this->save();

        return $this;
    }

    /**
     * Mark payment as failed
     */
    public function markFailed($reason = null, $gatewayResponse = null): self
    {
        $this->status = self::STATUS_FAILED;
        if ($reason) {
            $this->failed_reason = $reason;
        }
        if ($gatewayResponse) {
            $this->gateway_response = $gatewayResponse;
        }
        $this->save();

        return $this;
    }

    /**
     * Process refund request
     */
    public function refund($amount = null): bool
    {
        try {
            // Default refund amount = full payment
            $refundAmount = $amount ?? $this->amount;

            // Check jika amount valid
            if ($refundAmount > $this->amount) {
                return false;
            }

            // Check jika sudah di-refund sebelumnya
            if ($this->isRefunded()) {
                return false;
            }

            // Update payment record
            $this->status = self::STATUS_REFUNDED;
            $this->refund_amount = $refundAmount;
            $this->refunded_at = now();
            $this->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Calculate total amount dengan tax
     */
    public function getTotalWithTax(): float
    {
        $taxAmount = $this->taxRecords()
            ->where('status', 'calculated')
            ->sum('tax_amount');

        return $this->amount + $taxAmount;
    }

    /**
     * Get payment status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PROCESSING => 'Memproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_REFUNDED => 'Dikembalikan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get payment method label
     */
    public function getMethodLabel(): string
    {
        return match ($this->payment_method) {
            self::METHOD_STRIPE => 'Stripe (Kartu Kredit)',
            self::METHOD_GCASH => 'GCash',
            self::METHOD_BANK => 'Transfer Bank',
            self::METHOD_WALLET => 'E-Wallet',
            default => 'Metode Lain',
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2, ',', '.');
    }
}
