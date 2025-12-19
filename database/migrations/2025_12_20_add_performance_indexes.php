<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add compound indexes for better query performance
        // These reduce N+1 query problems and improve filter performance

        // For Konsultasi table - optimize status + date queries
        if (Schema::hasTable('consultations')) {
            // Check if indexes already exist before creating
            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_konsultasi_status_created'
            ");
            
            if (empty($indexExists)) {
                Schema::table('consultations', function (Blueprint $table) {
                    // Compound index for status filtering with date sorting
                    $table->index(['status', 'created_at'], 'idx_konsultasi_status_created');
                });
            }

            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_konsultasi_doctor_status'
            ");
            
            if (empty($indexExists)) {
                Schema::table('consultations', function (Blueprint $table) {
                    // Optimize doctor consultation queries
                    $table->index(['doctor_id', 'status'], 'idx_konsultasi_doctor_status');
                });
            }

            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_konsultasi_patient_status'
            ");
            
            if (empty($indexExists)) {
                Schema::table('consultations', function (Blueprint $table) {
                    // Optimize patient consultation queries
                    $table->index(['patient_id', 'status'], 'idx_konsultasi_patient_status');
                });
            }
        }

        // For Doctors table - optimize availability queries
        if (Schema::hasTable('doctors')) {
            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_doctors_available'
            ");
            
            if (empty($indexExists)) {
                Schema::table('doctors', function (Blueprint $table) {
                    $table->index('is_available', 'idx_doctors_available');
                });
            }

            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_doctors_verified_available'
            ");
            
            if (empty($indexExists)) {
                Schema::table('doctors', function (Blueprint $table) {
                    $table->index(['is_verified', 'is_available'], 'idx_doctors_verified_available');
                });
            }
        }

        // For Users table - optimize status queries
        if (Schema::hasTable('users')) {
            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_users_active'
            ");
            
            if (empty($indexExists)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('is_active', 'idx_users_active');
                });
            }

            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_users_email'
            ");
            
            if (empty($indexExists)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('email', 'idx_users_email');
                });
            }
        }

        // For Messages/Chat table
        if (Schema::hasTable('chat_messages')) {
            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_chat_messages_konsultasi'
            ");
            
            if (empty($indexExists)) {
                Schema::table('chat_messages', function (Blueprint $table) {
                    $table->index(['consultation_id', 'created_at'], 'idx_chat_messages_konsultasi');
                });
            }
        }

        // For Medical Records table
        if (Schema::hasTable('medical_records')) {
            $indexExists = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type='index' AND name='idx_medical_records_patient'
            ");
            
            if (empty($indexExists)) {
                Schema::table('medical_records', function (Blueprint $table) {
                    $table->index(['patient_id', 'created_at'], 'idx_medical_records_patient');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the indexes
        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropIndex('idx_konsultasi_status_created');
                $table->dropIndex('idx_konsultasi_doctor_status');
                $table->dropIndex('idx_konsultasi_patient_status');
            });
        }

        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropIndex('idx_doctors_available');
                $table->dropIndex('idx_doctors_verified_available');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_active');
                $table->dropIndex('idx_users_email');
            });
        }

        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropIndex('idx_chat_messages_konsultasi');
            });
        }

        if (Schema::hasTable('medical_records')) {
            Schema::table('medical_records', function (Blueprint $table) {
                $table->dropIndex('idx_medical_records_patient');
            });
        }
    }
};
