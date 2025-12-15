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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('scheduled_at');                      // Waktu appointment
            $table->dateTime('started_at')->nullable();            // Waktu dimulai
            $table->dateTime('ended_at')->nullable();              // Waktu selesai
            $table->enum('status', [
                'pending',      // Menunggu konfirmasi dokter
                'confirmed',    // Sudah dikonfirmasi
                'in_progress',  // Sedang berlangsung
                'completed',    // Selesai
                'cancelled',    // Dibatalkan pasien
                'rejected',     // Ditolak dokter
            ])->default('pending');
            $table->enum('type', [
                'text_consultation',    // Teks chat
                'video_call',           // Video call
                'phone_call',           // Phone call
            ])->default('text_consultation');
            $table->text('reason')->nullable();                    // Alasan appointment
            $table->text('notes')->nullable();                     // Catatan dokter
            $table->string('consultation_link')->nullable();       // Link untuk video/call
            $table->integer('duration_minutes')->default(30);      // Durasi appointment
            $table->decimal('price', 10, 2)->nullable();           // Harga konsultasi
            $table->string('payment_status')->default('pending');  // pending, paid, refunded
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');

            // Index untuk efficient queries
            $table->index(['patient_id', 'scheduled_at']);
            $table->index(['doctor_id', 'scheduled_at']);
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
