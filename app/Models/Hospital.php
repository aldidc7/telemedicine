<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $table = 'hospitals';
    
    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'city',
        'province',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all emergencies from this hospital
     */
    public function emergencies(): HasMany
    {
        return $this->hasMany(Emergency::class);
    }
}
