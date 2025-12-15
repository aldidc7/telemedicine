<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->string('nik', 20)->unique()
                ->comment('Nomor Identitas Kependudukan');
            
            $table->date('date_of_birth');
            
            $table->enum('gender', ['laki-laki', 'perempuan']);
            
            $table->text('address');
            
            $table->string('phone_number', 20);
            
            $table->string('emergency_contact_name')->nullable();
            
            $table->string('emergency_contact_phone', 20)->nullable();
            
            $table->enum('blood_type', ['O', 'A', 'B', 'AB'])
                ->nullable();
            
            $table->text('allergies')
                ->nullable()
                ->comment('JSON array atau teks');
            
            $table->timestamps();
            
            // Indexes
            $table->index('nik');
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};