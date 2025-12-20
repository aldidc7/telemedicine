<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create tables untuk emergency procedures
     * - emergencies: Main emergency record
     * - emergency_contacts: Contacts yang dihubungi
     * - emergency_escalation_logs: Immutable audit trail
     */
    public function up(): void
    {
        // Emergencies table
        if (!Schema::hasTable('emergencies')) {
            Schema::create('emergencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_id')->constrained('konsultasis')->onDelete('cascade');
                $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
                
                // Emergency level: critical, severe, moderate
                $table->enum('level', ['critical', 'severe', 'moderate'])->default('severe');
                
                // Emergency reason/symptoms
                $table->text('reason');
                
                // Status: open, escalated, resolved, referred
                $table->enum('status', ['open', 'escalated', 'resolved', 'referred'])->default('open');
                
                // Hospital referral info
                $table->unsignedBigInteger('hospital_id')->nullable();
                $table->string('hospital_name')->nullable();
                $table->text('hospital_address')->nullable();
                
                // Ambulance info
                $table->timestamp('ambulance_called_at')->nullable();
                $table->string('ambulance_eta')->nullable();
                
                // Escalation timestamp
                $table->timestamp('escalated_at')->nullable();
                
                // Referral letter
                $table->longText('referral_letter')->nullable();
                
                // Additional notes
                $table->text('notes')->nullable();
                
                $table->softDeletes();
                $table->timestamps();
                
                // Indexes untuk query
                $table->index('level');
                $table->index('status');
                $table->index('created_by_id');
                $table->index('created_at');
            });
        }

        // Emergency contacts table
        if (!Schema::hasTable('emergency_contacts')) {
            Schema::create('emergency_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('emergency_id')->constrained('emergencies')->onDelete('cascade');
                
                // Contact type: hospital, ambulance, police, family, etc
                $table->enum('type', ['hospital', 'ambulance', 'police', 'family', 'other']);
                
                // Contact info
                $table->string('name');
                $table->string('phone');
                $table->text('address')->nullable();
                
                // Status: pending, contacted, confirmed, unavailable
                $table->enum('status', ['pending', 'contacted', 'confirmed', 'unavailable'])->default('pending');
                
                // When contact was made
                $table->timestamp('contacted_at')->nullable();
                
                // Response from contact
                $table->text('response')->nullable();
                
                $table->timestamp('created_at')->nullable();
                
                $table->index('emergency_id');
                $table->index('type');
            });
        }

        // Emergency escalation logs (immutable audit trail)
        if (!Schema::hasTable('emergency_escalation_logs')) {
            Schema::create('emergency_escalation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('emergency_id')->constrained('emergencies')->onDelete('cascade');
                
                // Action: ambulance_called, hospital_escalation, contact_made, referral_generated, etc
                $table->string('action')->index();
                
                // Action details
                $table->text('details');
                
                // Timestamp (no updates allowed - immutable)
                $table->timestamp('timestamp')->index();
                
                // Prevent modification - this is audit log
                // No updated_at field
                
                $table->index(['emergency_id', 'timestamp']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_escalation_logs');
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('emergencies');
    }
};
