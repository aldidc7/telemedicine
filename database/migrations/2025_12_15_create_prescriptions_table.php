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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');
            
            // Medications stored as JSON array
            // Format: [{"name": "Paracetamol", "dosage": "500mg", "frequency": "2x sehari", "duration": "7 hari", "quantity": 14, "instructions": "Diminum setelah makan"}]
            $table->json('medications');
            
            // Additional notes from doctor
            $table->text('notes')->nullable();
            
            // Instructions for patient
            $table->text('instructions')->nullable();
            
            // Prescription status
            $table->enum('status', ['active', 'expired', 'completed'])->default('active');
            
            // When prescription was issued
            $table->timestamp('issued_at')->useCurrent();
            
            // When prescription expires (validity period)
            $table->dateTime('expires_at')->nullable();
            
            // Doctor notes after issuing
            $table->text('doctor_notes')->nullable();
            
            // Track if patient acknowledged
            $table->boolean('patient_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('doctor_id');
            $table->index('patient_id');
            $table->index('appointment_id');
            $table->index('status');
            $table->index('issued_at');
            $table->index('expires_at');
            $table->index(['patient_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
