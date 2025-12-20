<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VideoRecording Model
 * 
 * Menyimpan data rekaman video consultation
 * Tracks durasi, storage path, dan status deletion
 */
class VideoRecording extends Model
{
    use SoftDeletes;

    protected $table = 'video_recordings';

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'patient_id',
        'storage_path',
        'duration',
        'file_size',
        'jitsi_room_name',
        'is_deleted',
    ];

    protected $casts = [
        'duration' => 'integer',
        'file_size' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Konsultasi
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    /**
     * Relationship: Belongs to Doctor (User)
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Relationship: Belongs to Patient (User)
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get recording URL
     * 
     * @return string|null
     */
    public function getDownloadUrl(): ?string
    {
        if (!$this->storage_path) {
            return null;
        }

        return route('api.video-recording.download', [
            'recording' => $this->id,
        ]);
    }

    /**
     * Get duration in human readable format
     * 
     * @return string
     */
    public function getDurationFormatted(): string
    {
        if (!$this->duration) {
            return '0:00';
        }

        $minutes = intval($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get file size in human readable format
     * 
     * @return string
     */
    public function getFileSizeFormatted(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return sprintf('%.2f %s', $size, $units[$unitIndex]);
    }

    /**
     * Check if recording is accessible
     * 
     * @return bool
     */
    public function isAccessible(): bool
    {
        return !$this->is_deleted && $this->storage_path;
    }

    /**
     * Mark as deleted (soft delete)
     * 
     * @return void
     */
    public function markAsDeleted(): void
    {
        $this->update(['is_deleted' => true]);
        $this->delete(); // Soft delete
    }
}
