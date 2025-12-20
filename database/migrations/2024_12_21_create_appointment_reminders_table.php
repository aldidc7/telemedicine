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
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('user_id');

            // Reminder configuration
            $table->enum('reminder_type', ['sms', 'email', 'push'])->default('sms');
            $table->dateTime('scheduled_for'); // When to send

            // Delivery status
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['appointment_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('scheduled_for');

            // Foreign keys
            $table->foreign('appointment_id')
                ->references('id')
                ->on('konsultasi')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
    }
};
