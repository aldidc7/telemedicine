<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceItem Model
 * 
 * Line items dalam invoice
 * Contoh: Consultation fee, Emergency charge, Discount, etc
 */
class InvoiceItem extends Model
{
    const UPDATED_AT = null; // Immutable
    
    protected $fillable = [
        'invoice_id',
        'description',
        'item_type',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    const TYPE_CONSULTATION = 'consultation';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_SERVICE = 'service';
    const TYPE_DISCOUNT = 'discount';

    /**
     * Relationship ke invoice
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
