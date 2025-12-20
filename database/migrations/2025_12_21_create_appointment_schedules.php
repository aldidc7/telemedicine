<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Doctor Availability Schedule
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedInteger('slot_duration_minutes')->default(30);
            $table->unsignedInteger('max_appointments_per_day')->default(20);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('doctor_id');
            $table->index('day_of_week');
            $table->unique(['doctor_id', 'day_of_week']);
        });

        // Time Slots (Generated from availability)
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_availability_id')->constrained('doctor_availabilities')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamp('booked_at')->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('doctor_availability_id');
            $table->index('date');
            $table->index('is_available');
            $table->unique(['doctor_availability_id', 'date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('doctor_availabilities');
    }
};
