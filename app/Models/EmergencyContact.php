<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * EMERGENCY CONTACT MODEL
 * ============================================
 * 
 * Track emergency contacts yang dihubungi
 * (hospital, ambulance, police, family, etc)
 * 
 * @property int $id
 * @property int $emergency_id
 * @property string $type - hospital, ambulance, police, family
 * @property string $name - Contact name
 * @property string $phone - Contact phone
 * @property string|null $address - Contact address
 * @property string $status - pending, contacted, confirmed, unavailable
 * @property timestamp $contacted_at
 * @property string|null $response - Response dari contact
 * @property timestamp $created_at
 */
class EmergencyContact extends Model
{
    use HasFactory;

    public $timestamps = ['created_at'];
    public const UPDATED_AT = null;

    protected $fillable = [
        'emergency_id',
        'type',
        'name',
        'phone',
        'address',
        'status',
        'contacted_at',
        'response',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }
}
