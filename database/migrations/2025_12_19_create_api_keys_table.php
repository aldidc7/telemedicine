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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "SIMRS Integration"
            $table->string('key')->unique(); // The actual API key
            $table->string('secret')->nullable(); // Secret for additional security
            $table->string('type')->default('general'); // general, simrs, webhook, etc
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // What this key can access
            $table->integer('rate_limit')->default(1000); // Requests per hour
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('key');
            $table->index('type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
