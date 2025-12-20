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
        if (!Schema::hasTable('consent_logs')) {
            Schema::create('consent_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                
                // Type of consent: 'privacy_policy', 'terms_of_service', 'data_processing'
                $table->string('consent_type')->index();
                
                // Status: 'accepted', 'rejected', 'pending', 'revoked'
                $table->string('status')->default('pending')->index();
                
                // IP address where consent was given/denied
                $table->string('ip_address')->nullable();
                
                // User agent (browser info)
                $table->text('user_agent')->nullable();
                
                $table->timestamps();
                
                // Index untuk filtering
                $table->index(['user_id', 'consent_type']);
                $table->index(['consent_type', 'status']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
