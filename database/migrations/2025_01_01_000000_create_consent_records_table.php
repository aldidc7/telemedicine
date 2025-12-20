<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table for tracking user informed consent for telemedicine usage.
     * Compliance requirement for telemedicine regulations (Wikipedia: Telemedicine ethics)
     * 
     * Informed consent is REQUIRED before patient can book first consultation.
     */
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Type of consent: 'telemedicine', 'data_processing', 'privacy_policy'
            $table->string('consent_type')->index();
            
            // Content of what was consented to
            $table->longText('consent_text')->nullable();
            
            // Whether user accepted
            $table->boolean('accepted')->default(false)->index();
            
            // When they accepted
            $table->timestamp('accepted_at')->nullable()->index();
            
            // User's IP address when accepting
            $table->ipAddress('ip_address')->nullable();
            
            // User's browser/device info
            $table->text('user_agent')->nullable();
            
            // Metadata about consent version
            $table->string('version')->default('1.0');
            
            // For future: track if user revoked consent
            $table->timestamp('revoked_at')->nullable();
            
            $table->timestamps();
            
            // Indices for quick lookups
            $table->index(['user_id', 'consent_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
