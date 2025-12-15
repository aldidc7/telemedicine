<?php

namespace App\Database;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan indexes untuk query optimization
     * Fokus pada frequently queried dan joined columns
     */
    public function up(): void
    {
        // Consultations table indexes
        Schema::table('consultations', function (Blueprint $table) {
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('status');
            $table->index(['status', 'created_at']);
            $table->index('updated_at');
        });

        // ChatMessages table indexes
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('consultation_id');
            $table->index('sender_id');
            $table->index('read_at');
            $table->index(['consultation_id', 'read_at']);
            $table->index('created_at');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('created_at');
        });

        // Patients table indexes
        Schema::table('patients', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('nik');
        });

        // Doctors table indexes
        Schema::table('doctors', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('specialization');
            $table->index('is_available');
        });

        // Ratings table indexes
        Schema::table('ratings', function (Blueprint $table) {
            $table->index('consultation_id');
            $table->index('created_at');
        });

        // MedicalRecords table indexes
        Schema::table('medical_records', function (Blueprint $table) {
            $table->index('consultation_id');
            $table->index('doctor_id');
            $table->index('created_at');
        });

        // ActivityLogs table indexes
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['consultation_id']);
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['consultation_id', 'read_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['nik']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['specialization']);
            $table->dropIndex(['is_available']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['consultation_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex(['consultation_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['model_type']);
            $table->dropIndex(['model_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
