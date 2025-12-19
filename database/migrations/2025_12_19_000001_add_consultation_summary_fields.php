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
        // ============================================
        // ADD CONSULTATION SUMMARY FIELDS
        // ============================================
        // Menambahkan fitur consultation summary (kesimpulan konsultasi)
        // yang penting untuk dokumentasi medis
        
        Schema::table('consultations', function (Blueprint $table) {
            // Diagnosis & Medical Info
            $table->text('diagnosis')->nullable()->after('status')->comment('Diagnosis from doctor');
            $table->text('findings')->nullable()->after('diagnosis')->comment('Clinical findings');
            $table->text('treatment_plan')->nullable()->after('findings')->comment('Treatment plan');
            
            // Follow-up
            $table->date('follow_up_date')->nullable()->after('treatment_plan')->comment('Scheduled follow-up date');
            $table->text('follow_up_instructions')->nullable()->after('follow_up_date')->comment('Instructions for patient');
            
            // Summary Status
            $table->boolean('summary_completed')->default(false)->after('follow_up_instructions')->comment('Whether summary has been filled');
            $table->timestamp('summary_completed_at')->nullable()->after('summary_completed')->comment('When summary was completed');
            
            // Medications & Prescriptions
            $table->json('medications')->nullable()->after('summary_completed_at')->comment('List of prescribed medications');
            
            // Additional Notes
            $table->text('notes')->nullable()->after('medications')->comment('Additional notes from doctor');
        });

        // ============================================
        // CREATE CONSULTATION SUMMARIES TABLE (Detailed)
        // ============================================
        // Tabel terpisah untuk keeping detailed summary history
        
        Schema::create('consultation_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            
            // Summary Content
            $table->text('diagnosis')->comment('Diagnosis');
            $table->text('clinical_findings')->nullable()->comment('Hasil pemeriksaan klinis');
            $table->text('examination_results')->nullable()->comment('Hasil pemeriksaan tambahan');
            $table->text('treatment_plan')->comment('Rencana pengobatan');
            
            // Follow-up
            $table->date('follow_up_date')->nullable()->comment('Tanggal follow-up');
            $table->text('follow_up_instructions')->nullable()->comment('Instruksi follow-up');
            
            // Medications
            $table->json('medications')->nullable()->comment('Daftar obat yang diresepkan');
            $table->json('referrals')->nullable()->comment('Referral ke spesialis lain jika ada');
            
            // Additional
            $table->text('additional_notes')->nullable()->comment('Catatan tambahan');
            $table->boolean('patient_acknowledged')->default(false)->comment('Pasien sudah lihat summary');
            $table->timestamp('patient_acknowledged_at')->nullable()->comment('Kapan pasien lihat summary');
            
            $table->timestamps();
            
            // Indexes
            $table->index('consultation_id');
            $table->index('doctor_id');
            $table->index('created_at');
        });

        // ============================================
        // MEDICATIONS TABLE (For detailed medication records)
        // ============================================
        
        Schema::create('consultation_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            
            // Medication Info
            $table->string('medication_name', 255)->comment('Nama obat');
            $table->string('dose', 100)->comment('Dosis (contoh: 500mg)');
            $table->string('frequency', 100)->comment('Frekuensi (contoh: 3x sehari)');
            $table->integer('duration_days')->comment('Durasi pengobatan dalam hari');
            $table->text('instructions')->nullable()->comment('Instruksi khusus');
            
            // Prescription Info
            $table->string('route', 50)->default('oral')->comment('Route (oral, injection, topical, etc)');
            $table->boolean('is_active')->default(true)->comment('Apakah masih aktif');
            
            // Prescription Status
            $table->string('status')->default('prescribed')->comment('prescribed, filled, completed');
            $table->timestamp('prescribed_at')->nullable();
            $table->timestamp('filled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('consultation_id');
            $table->index('doctor_id');
            $table->index('created_at');
        });

        // ============================================
        // CONSULTATION FOLLOW-UP TABLE
        // ============================================
        
        Schema::create('consultation_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_consultation_id')->constrained('consultations', 'id')->onDelete('cascade');
            $table->foreignId('follow_up_consultation_id')->nullable()->constrained('consultations', 'id')->onDelete('set null');
            
            $table->string('status')->default('scheduled')->comment('scheduled, completed, cancelled, no-show');
            $table->date('scheduled_date')->comment('Tanggal follow-up dijadwalkan');
            $table->text('reason')->nullable()->comment('Alasan follow-up');
            
            $table->timestamps();
            
            // Indexes
            $table->index('original_consultation_id');
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_follow_ups');
        Schema::dropIfExists('consultation_medications');
        Schema::dropIfExists('consultation_summaries');
        
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'diagnosis',
                'findings',
                'treatment_plan',
                'follow_up_date',
                'follow_up_instructions',
                'summary_completed',
                'summary_completed_at',
                'medications',
                'notes',
            ]);
        });
    }
};
