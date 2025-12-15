<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->string('action')
                ->comment('created_consultation, sent_message, dll');
            
            $table->string('model_type')
                ->nullable()
                ->comment('Model yang dimodifikasi');
            
            $table->bigInteger('model_id')
                ->nullable()
                ->comment('ID dari model yang dimodifikasi');
            
            $table->json('old_values')
                ->nullable()
                ->comment('Nilai sebelum perubahan');
            
            $table->json('new_values')
                ->nullable()
                ->comment('Nilai sesudah perubahan');
            
            $table->text('description')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};