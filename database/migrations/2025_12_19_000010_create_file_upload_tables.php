<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracking file uploads untuk audit trail
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('category'); // profile_photo, medical_document, dll
            $table->enum('status', ['active', 'trashed', 'deleted'])->default('active');
            $table->bigInteger('file_size'); // bytes
            $table->string('mime_type');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('permanently_deleted_at')->nullable();

            // Indexes
            $table->index('user_id');
            $table->index('category');
            $table->index('status');
            $table->index('uploaded_at');
        });

        // Track user storage usage
        Schema::create('user_storage_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('user_role', ['patient', 'doctor', 'hospital', 'admin'])->default('patient');
            $table->bigInteger('max_storage'); // bytes
            $table->bigInteger('used_storage')->default(0); // bytes
            $table->bigInteger('last_sync')->useCurrent();
            $table->timestamps();

            // Unique constraint per user
            $table->unique('user_id');
            $table->index('user_role');
        });

        // Cleanup schedule log
        Schema::create('file_cleanup_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('files_deleted');
            $table->bigInteger('space_freed'); // bytes
            $table->timestamp('cleanup_date')->useCurrent();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index('cleanup_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
        Schema::dropIfExists('user_storage_quotas');
        Schema::dropIfExists('file_cleanup_logs');
    }
};
