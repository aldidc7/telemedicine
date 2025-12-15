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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_1_id');      // Pasien atau Dokter
            $table->unsignedBigInteger('user_2_id');      // Dokter atau Pasien
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_1_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_2_id')->references('id')->on('users')->onDelete('cascade');

            // Index untuk quick lookup
            $table->index(['user_1_id', 'user_2_id']);
            $table->unique(['user_1_id', 'user_2_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
