<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates tables untuk:
     * 1. patient_data_access_log - Track semua akses ke patient data
     * 2. patient_deletion_requests - Track data deletion requests (GDPR)
     */
    public function up(): void
    {
        // Patient Data Access Log
        // GDPR/Privacy compliance: Track semua akses ke patient data
        Schema::create('patient_data_access_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('action'); // 'medical_records_view', 'export_pdf', dll
            $table->unsignedBigInteger('target_id')->nullable(); // ID dari resource yang diakses
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('accessed_at');
            $table->timestamps();
            
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index(['patient_id', 'accessed_at']);
        });

        // Patient Deletion Requests
        // Right to be Forgotten (GDPR requirement)
        Schema::create('patient_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->text('reason');
            $table->enum('status', [
                'pending',    // Menunggu review admin
                'approved',   // Disetujui, data dihapus
                'rejected',   // Ditolak
                'processing', // Sedang diproses
            ])->default('pending');
            $table->dateTime('requested_at');
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Admin yang review
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('reviewed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Add indexes untuk performance
        Schema::table('patient_data_access_log', function (Blueprint $table) {
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_deletion_requests');
        Schema::dropIfExists('patient_data_access_log');
    }
};
