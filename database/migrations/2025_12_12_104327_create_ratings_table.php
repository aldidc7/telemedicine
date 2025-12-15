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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('dokter_id')->constrained()->onDelete('cascade');
            $table->foreignId('konsultasi_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->min(1)->max(5); // 1-5 stars
            $table->text('review')->nullable();
            $table->timestamps();
            
            // Unique constraint: one rating per consultation
            $table->unique(['user_id', 'konsultasi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
