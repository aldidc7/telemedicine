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
        // Create rate_limits table untuk track rate limiting
        if (!Schema::hasTable('rate_limits')) {
            Schema::create('rate_limits', function (Blueprint $table) {
                $table->id();
                $table->string('key'); // 'login:192.168.1.1', 'register:email@example.com'
                $table->integer('attempts')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamp('reset_at')->nullable();
                $table->timestamps();
                
                $table->unique('key');
                $table->index('reset_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limits');
    }
};
