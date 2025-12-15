<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->onDelete('cascade');
            
            $table->string('record_type')
                ->comment('history, diagnosis, lab_result, dll');
            
            $table->json('data')
                ->comment('Flexible JSON dari SIMRS atau input lokal');
            
            $table->date('record_date');
            
            $table->string('source', 50)
                ->default('SIMRS')
                ->comment('SIMRS atau LOCAL');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('patient_id');
            $table->index('record_type');
            $table->index('record_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};