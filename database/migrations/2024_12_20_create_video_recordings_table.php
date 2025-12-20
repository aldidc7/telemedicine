<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('storage_path')->nullable();
            $table->string('jitsi_room_name')->nullable();
            $table->unsignedInteger('duration')->default(0); // seconds
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('consultation_id');
            $table->index('doctor_id');
            $table->index('patient_id');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('consultation_id')
                ->references('id')
                ->on('konsultasis')
                ->onDelete('cascade');
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_recordings');
    }
};
