<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add MRN dan encrypted NIK untuk compliance & security
     * Plus create audit_logs table
     */
    public function up(): void
    {
        // ===== ALTER PATIENTS TABLE - ADD MRN & ENCRYPTED NIK =====
        Schema::table('patients', function (Blueprint $table) {
            // Tambah MRN (Medical Record Number) - format: RM-YYYY-XXXXX
            if (!Schema::hasColumn('patients', 'medical_record_number')) {
                $table->string('medical_record_number', 50)
                    ->unique()
                    ->nullable()
                    ->after('user_id')
                    ->comment('Medical Record Number - format: RM-YYYY-XXXXX');
            }
            
            // Tambah encrypted_nik untuk keamanan PII
            if (!Schema::hasColumn('patients', 'encrypted_nik')) {
                $table->string('encrypted_nik', 255)
                    ->nullable()
                    ->after('medical_record_number')
                    ->comment('Nomor Identitas Kependudukan (Encrypted)');
            }
        });

        // ===== CREATE AUDIT LOGS TABLE =====
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                
                // Who accessed
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('cascade')
                    ->comment('User yang akses');
                
                // What was accessed
                $table->string('entity_type')
                    ->comment('Type: patient, medical_record, etc');
                
                $table->unsignedBigInteger('entity_id')
                    ->comment('ID of entity yang diakses');
                
                $table->string('action')
                    ->comment('Action: view, create, update, delete, download');
                
                // Details
                $table->text('description')->nullable();
                $table->json('changes')->nullable()
                    ->comment('What changed (JSON format)');
                
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                
                // PII access tracking
                $table->boolean('accessed_pii')->default(false)
                    ->comment('Did this action access PII?');
                
                $table->enum('access_level', [
                    'public',
                    'protected',
                    'confidential',
                    'highly_confidential'
                ])->default('protected');
                
                $table->timestamp('created_at');
                
                // Indexes
                $table->index('user_id');
                $table->index('entity_type');
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'medical_record_number')) {
                $table->dropIndex(['medical_record_number']);
                $table->dropColumn('medical_record_number');
            }
            
            if (Schema::hasColumn('patients', 'encrypted_nik')) {
                $table->dropColumn('encrypted_nik');
            }
        });
    }
};
