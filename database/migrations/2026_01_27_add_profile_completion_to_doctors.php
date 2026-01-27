<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Profile completion status
            $table->enum('registration_status', ['pending', 'completed', 'rejected'])
                ->default('pending')
                ->after('specialization')
                ->comment('pending: belum lengkap, completed: lengkap, rejected: ditolak admin');
            
            $table->enum('document_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('registration_status')
                ->comment('Status verifikasi dokumen oleh admin');
            
            // Document fields
            $table->string('sip_file_path')->nullable()->after('address');
            $table->string('str_file_path')->nullable()->after('sip_file_path');
            $table->string('ktp_file_path')->nullable()->after('str_file_path');
            $table->string('ijazah_file_path')->nullable()->after('ktp_file_path');
            $table->json('additional_documents')->nullable()->after('ijazah_file_path');
            
            // Timestamps for profile completion
            $table->timestamp('profile_completed_at')->nullable()->after('additional_documents');
            $table->timestamp('document_verified_at')->nullable()->after('profile_completed_at');
            $table->unsignedBigInteger('verified_by_admin_id')->nullable()->after('document_verified_at');
            $table->text('verification_notes')->nullable()->after('verified_by_admin_id');
            
            // Compliance fields
            $table->boolean('accepted_terms')->default(false)->after('verification_notes');
            $table->boolean('accepted_privacy_policy')->default(false)->after('accepted_terms');
            $table->boolean('accepted_informed_consent')->default(false)->after('accepted_privacy_policy');
            $table->timestamp('compliance_accepted_at')->nullable()->after('accepted_informed_consent');
            
            // Add indexes
            $table->index('registration_status');
            $table->index('document_status');
            $table->foreign('verified_by_admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['verified_by_admin_id']);
            $table->dropColumn([
                'registration_status',
                'document_status',
                'sip_file_path',
                'str_file_path',
                'ktp_file_path',
                'ijazah_file_path',
                'additional_documents',
                'profile_completed_at',
                'document_verified_at',
                'verified_by_admin_id',
                'verification_notes',
                'accepted_terms',
                'accepted_privacy_policy',
                'accepted_informed_consent',
                'compliance_accepted_at',
            ]);
        });
    }
};
