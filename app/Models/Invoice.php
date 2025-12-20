<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice Model
 * 
 * Represents invoice untuk payment tracking
 * Generated otomatis dari payment record
 * 
 * Methods:
 * - generateNumber(): Generate unique invoice number
 * - markSent(): Mark invoice sebagai terkirim
 * - markPaid(): Mark invoice sebagai terbayar
 * - getTotal(): Hitung total dengan tax
 * - sendEmail(): Send invoice ke email
 * 
 * Relationships:
 * - payment: Payment yang menghasilkan invoice
 * - user: User pembayar
 * - items: Line items dalam invoice
 */
class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Invoice status: draft, sent, overdue, paid, cancelled
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // ==================== RELATIONSHIPS ====================

    /**
     * Payment yang menghasilkan invoice
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * User pembayar
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Line items dalam invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Filter draft invoices
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Filter sent invoices
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Filter paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Filter overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->where('due_date', '<', now()->date());
    }

    /**
     * Filter by invoice number
     */
    public function scopeByNumber($query, $number)
    {
        return $query->where('invoice_number', $number);
    }

    /**
     * Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter invoices dalam date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    // ==================== METHODS ====================

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXXXX
     */
    public static function generateNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('INV-%s-%05d', $date, $count);
    }

    /**
     * Mark invoice sebagai terkirim
     */
    public function markSent(): self
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark invoice sebagai terbayar
     */
    public function markPaid(): self
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        $this->save();

        // Update related payment
        if ($this->payment) {
            $this->payment->markCompleted();
        }

        return $this;
    }

    /**
     * Mark invoice sebagai overdue
     */
    public function markOverdue(): self
    {
        if ($this->status !== self::STATUS_PAID && $this->due_date < now()->date()) {
            $this->status = self::STATUS_OVERDUE;
            $this->save();
        }

        return $this;
    }

    /**
     * Cancel invoice
     */
    public function cancel(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return $this;
    }

    /**
     * Get total amount
     */
    public function getTotal(): float
    {
        return (float) $this->total_amount;
    }

    /**
     * Get subtotal
     */
    public function getSubtotal(): float
    {
        return (float) $this->subtotal;
    }

    /**
     * Get tax amount
     */
    public function getTaxAmount(): float
    {
        return (float) $this->tax_amount;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        return (float) $this->discount_amount;
    }

    /**
     * Calculate days until due
     */
    public function getDaysUntilDue(): int
    {
        return now()->diffInDays($this->due_date);
    }

    /**
     * Check jika invoice overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now()->date() && $this->status !== self::STATUS_PAID;
    }

    /**
     * Check jika invoice paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_OVERDUE => 'Jatuh Tempo',
            self::STATUS_PAID => 'Terbayar',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
}
