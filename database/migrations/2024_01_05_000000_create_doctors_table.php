<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->string('specialization')
                ->comment('Dokter Anak, Umum, dll');
            
            $table->string('license_number', 50)
                ->unique()
                ->comment('SIP/STR');
            
            $table->string('phone_number', 20)->nullable();
            
            $table->boolean('is_available')
                ->default(true);
            
            $table->integer('max_concurrent_consultations')
                ->default(5);
            
            $table->timestamps();
            
            // Indexes
            $table->index('specialization');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};