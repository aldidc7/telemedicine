<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates doctor_patient_relationships table untuk track hubungan dokter-pasien.
     * Requirement Ryan Haight Act: Harus ada established relationship sebelum telemedicine.
     */
    public function up(): void
    {
        Schema::create('doctor_patient_relationships', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('doctor_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            
            // Unique constraint: satu hubungan per dokter-pasien pair
            $table->unique(['doctor_id', 'patient_id']);
            
            // Establishment information (Ryan Haight Act)
            $table->enum('establishment_method', [
                'consultation',      // Dari konsultasi sebelumnya
                'doctor_initiated',  // Dokter yang menginisiasi
                'referral',         // Rujukan dari dokter lain
                'emergency',        // Situasi darurat medis
                'patient_request',  // Permintaan pasien dengan verifikasi
            ])->default('consultation');
            
            // Status
            $table->enum('status', [
                'active',
                'inactive', 
                'suspended',
                'terminated',
            ])->default('active');
            
            // Timeline
            $table->dateTime('established_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('last_access_at')->nullable();
            $table->dateTime('terminated_at')->nullable();
            
            // Termination details
            $table->string('termination_reason')->nullable();
            
            // Additional notes
            $table->longText('notes')->nullable();
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes untuk query performance
            $table->index('status');
            $table->index('establishment_method');
            $table->index('established_at');
            $table->index('last_access_at');
        });
        
        // Create audit log table untuk doctor_patient_relationships
        Schema::create('doctor_patient_relationship_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('relationship_id')->index();
            $table->string('action'); // 'created', 'updated', 'terminated', 'accessed'
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('user_type')->nullable(); // 'doctor', 'patient', 'admin'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->foreign('relationship_id')
                  ->references('id')
                  ->on('doctor_patient_relationships')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_patient_relationship_audit');
        Schema::dropIfExists('doctor_patient_relationships');
    }
};
