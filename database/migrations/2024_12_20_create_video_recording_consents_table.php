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
        Schema::create('video_recording_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->boolean('consented_to_recording')->default(false);
            $table->text('consent_reason')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('consent_given_at')->nullable();
            $table->dateTime('withdrawn_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['consultation_id', 'patient_id']);
            $table->index('doctor_id');
            $table->index('consent_given_at');

            // Foreign keys
            $table->foreign('consultation_id')
                ->references('id')
                ->on('konsultasis')
                ->onDelete('cascade');
            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('doctor_id')
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
        Schema::dropIfExists('video_recording_consents');
    }
};
