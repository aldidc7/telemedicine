<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->onDelete('cascade');
            
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->onDelete('set null');
            
            $table->string('complaint_type')
                ->comment('Demam, Batuk, dll');
            
            $table->text('description');
            
            $table->enum('status', ['pending', 'active', 'closed', 'cancelled'])
                ->default('pending');
            
            $table->timestamp('start_time')
                ->nullable()
                ->comment('Waktu dokter mulai handle');
            
            $table->timestamp('end_time')
                ->nullable()
                ->comment('Waktu konsultasi berakhir');
            
            $table->text('closing_notes')
                ->nullable()
                ->comment('Catatan penutup dari dokter');
            
            $table->boolean('simrs_synced')
                ->default(false)
                ->comment('Sudah sync ke SIMRS?');
            
            $table->timestamp('synced_at')
                ->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};