<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * SMS Template Model
 * Stores SMS message templates for different notification types
 */
class SMSTemplate extends Model
{
    use HasFactory;

    protected $table = 'sms_templates';

    protected $fillable = [
        'type',
        'title',
        'message',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'json',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: Get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get template by type
     */
    public static function findByType($type)
    {
        return static::where('type', $type)->active()->first();
    }

    /**
     * Build message with variables
     */
    public function buildMessage($data = [])
    {
        $message = $this->message;

        foreach ($data as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
            $message = str_replace("{{{$key}}}", $value, $message);
        }

        return $message;
    }
}
