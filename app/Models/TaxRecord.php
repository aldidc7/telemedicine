<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxRecord Model
 * 
 * Tracks tax calculations untuk compliance
 * Mendukung: PPh (Personal Income Tax), PPN (Value Added Tax)
 * 
 * Tax Types:
 * - pph: Personal Income Tax (15% untuk professional services)
 * - ppn: Value Added Tax (11% standard rate)
 * - other: Tax jenis lain
 * 
 * Methods:
 * - calculate(): Calculate tax berdasarkan amount & type
 * - report(): Mark sebagai reported untuk accounting
 * - getPercentage(): Get tax rate percentage
 */
class TaxRecord extends Model
{
    protected $fillable = [
        'payment_id',
        'tax_type',
        'tax_rate',
        'base_amount',
        'tax_amount',
        'status',
        'notes',
        'calculated_at',
        'reported_at',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
        'reported_at' => 'datetime',
    ];

    const UPDATED_AT = null; // Immutable - tidak bisa diupdate

    /**
     * Tax types
     */
    const TYPE_PPH = 'pph';           // Personal Income Tax
    const TYPE_PPN = 'ppn';           // Value Added Tax
    const TYPE_OTHER = 'other';

    /**
     * Tax status
     */
    const STATUS_CALCULATED = 'calculated';
    const STATUS_REPORTED = 'reported';

    /**
     * Standard tax rates (Indonesia)
     */
    const RATES = [
        'pph' => 15.00,      // 15% untuk professional services
        'ppn' => 11.00,      // 11% standard VAT rate
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Payment terkait
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Filter calculated taxes
     */
    public function scopeCalculated($query)
    {
        return $query->where('status', self::STATUS_CALCULATED);
    }

    /**
     * Filter reported taxes
     */
    public function scopeReported($query)
    {
        return $query->where('status', self::STATUS_REPORTED);
    }

    /**
     * Filter by tax type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    /**
     * Filter date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('calculated_at', [$startDate, $endDate]);
    }

    // ==================== METHODS ====================

    /**
     * Calculate tax berdasarkan amount & type
     * 
     * @param float $amount Base amount to tax
     * @param string $type Tax type (pph, ppn, etc)
     * @return float Tax amount
     */
    public static function calculate(float $amount, string $type = self::TYPE_PPN): float
    {
        $rate = self::RATES[$type] ?? 0;
        return ($amount * $rate) / 100;
    }

    /**
     * Get tax rate percentage untuk jenis ini
     */
    public function getPercentage(): float
    {
        return (float) $this->tax_rate;
    }

    /**
     * Mark tax sebagai reported
     */
    public function markReported(): self
    {
        $this->status = self::STATUS_REPORTED;
        $this->reported_at = now();
        $this->save();

        return $this;
    }

    /**
     * Check jika sudah reported
     */
    public function isReported(): bool
    {
        return $this->status === self::STATUS_REPORTED;
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format($this->tax_amount, 0, ',', '.');
    }

    /**
     * Get tax type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->tax_type) {
            self::TYPE_PPH => 'PPh (Pajak Penghasilan)',
            self::TYPE_PPN => 'PPN (Pajak Pertambahan Nilai)',
            self::TYPE_OTHER => 'Pajak Lainnya',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_CALCULATED => 'Dihitung',
            self::STATUS_REPORTED => 'Dilaporkan',
            default => 'Tidak Diketahui',
        };
    }
}
