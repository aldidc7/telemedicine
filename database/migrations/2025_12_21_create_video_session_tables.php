<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create video consultation session tables:
     * - video_sessions: Main video session records
     * - video_participant_logs: Immutable participant activity logs
     * - video_session_events: Immutable session events for analytics
     */
    public function up(): void
    {
        // Video Sessions table
        if (!Schema::hasTable('video_sessions')) {
            Schema::create('video_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('konsultasis')->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
                
                // Session Details
                $table->string('room_id')->unique();
                $table->enum('status', ['pending', 'ringing', 'active', 'paused', 'ended', 'cancelled', 'missed'])->default('pending');
                
                // Timestamps
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->unsignedInteger('duration_seconds')->nullable();
                
                // Recording
                $table->boolean('is_recorded')->default(false);
                $table->text('recording_url')->nullable();
                $table->unsignedBigInteger('recording_size_bytes')->nullable();
                
                // Session Features
                $table->boolean('screen_sharing_enabled')->default(false);
                
                // Call Quality
                $table->enum('call_quality', ['excellent', 'good', 'fair', 'poor'])->nullable();
                
                // End Info
                $table->enum('ended_reason', ['normal', 'timeout', 'patient_disconnect', 'doctor_disconnect', 'network_error', 'user_ended'])->nullable();
                $table->text('notes')->nullable();
                
                $table->softDeletes();
                $table->timestamps();
                
                // Indexes
                $table->index('doctor_id');
                $table->index('patient_id');
                $table->index('status');
                $table->index('created_at');
                $table->index(['consultation_id', 'status']);
            });
        }

        // Video Participant Logs table (immutable)
        if (!Schema::hasTable('video_participant_logs')) {
            Schema::create('video_participant_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('video_session_id')->constrained('video_sessions')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                
                // Event Details
                $table->enum('event_type', [
                    'joined', 'left',
                    'audio_enabled', 'audio_disabled',
                    'video_enabled', 'video_disabled',
                    'screen_shared', 'screen_unshared',
                    'network_degraded', 'network_recovered'
                ]);
                
                $table->timestamp('timestamp');
                
                // Connection Info (snapshot)
                $table->string('ip_address')->nullable();
                $table->enum('device_type', ['desktop', 'laptop', 'tablet', 'mobile'])->nullable();
                $table->text('browser_info')->nullable();
                
                // State at Event
                $table->enum('connection_quality', ['excellent', 'good', 'fair', 'poor'])->nullable();
                $table->boolean('audio_enabled')->default(false);
                $table->boolean('video_enabled')->default(false);
                $table->boolean('screen_shared')->default(false);
                
                // Immutable - no updated_at
                
                // Indexes
                $table->index('video_session_id');
                $table->index('user_id');
                $table->index('event_type');
                $table->index('timestamp');
                $table->index(['video_session_id', 'user_id']);
            });
        }

        // Video Session Events table (immutable)
        if (!Schema::hasTable('video_session_events')) {
            Schema::create('video_session_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('video_session_id')->constrained('video_sessions')->onDelete('cascade');
                
                // Event Details
                $table->string('event_type');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
                $table->text('description');
                $table->json('details')->nullable();
                
                $table->timestamp('timestamp');
                
                // Immutable - no updated_at
                
                // Indexes
                $table->index('video_session_id');
                $table->index('event_type');
                $table->index('severity');
                $table->index('timestamp');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_session_events');
        Schema::dropIfExists('video_participant_logs');
        Schema::dropIfExists('video_sessions');
    }
};
