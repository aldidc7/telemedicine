<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * EMERGENCY ESCALATION LOG MODEL
 * ============================================
 * 
 * Immutable audit trail untuk emergency escalation
 * 
 * @property int $id
 * @property int $emergency_id
 * @property string $action - ambulance_called, hospital_escalation, contact_made, etc
 * @property string $details - Action details
 * @property timestamp $timestamp
 */
class EmergencyEscalationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'emergency_id',
        'action',
        'details',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    // No update/delete - immutable audit log
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->timestamp) {
                $model->timestamp = now();
            }
        });
    }

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }
}
