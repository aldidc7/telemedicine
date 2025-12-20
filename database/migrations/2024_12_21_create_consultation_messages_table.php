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
        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('sender_id');

            // Content
            $table->longText('message');
            $table->string('file_url')->nullable();
            $table->enum('file_type', ['image', 'document', 'prescription'])->nullable();

            // Read Status
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performa query
            $table->index(['consultation_id', 'is_read']);
            $table->index(['consultation_id', 'created_at']);
            $table->index('sender_id');

            // Foreign Key Constraints
            $table->foreign('consultation_id')
                ->references('id')
                ->on('konsultasi')
                ->onDelete('cascade');

            $table->foreign('sender_id')
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
        Schema::dropIfExists('consultation_messages');
    }
};
