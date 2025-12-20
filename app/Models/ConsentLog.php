<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentLog extends Model
{
    protected $table = 'consent_logs';

    protected $fillable = [
        'user_id',
        'consent_type',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
