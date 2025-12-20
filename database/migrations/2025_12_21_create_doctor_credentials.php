<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Doctor Credentials Storage
        Schema::create('doctor_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('credential_type', ['kki', 'sip', 'aipki', 'spesialis', 'subspesialis']);
            $table->string('credential_number')->index();
            $table->string('issued_by')->nullable();
            $table->date('issued_date');
            $table->date('expiry_date')->index();
            $table->string('document_url')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'under_review', 'expired'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('doctor_id');
            $table->index('status');
            $table->unique(['doctor_id', 'credential_type', 'credential_number']);
        });

        // Doctor Verification Summary
        Schema::create('doctor_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('kkmi_number')->nullable()->index();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kki_number')->nullable();
            $table->string('sip_number')->nullable();
            $table->string('specialization')->nullable();
            $table->string('facility_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('verification_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_verifications');
        Schema::dropIfExists('doctor_credentials');
    }
};
