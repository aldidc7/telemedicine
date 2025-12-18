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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('action')->index(); // create, read, update, delete, download, export
            $table->string('resource')->index(); // dokter, pasien, user, konsultasi, config, etc
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->json('changes')->nullable(); // Store what changed
            $table->string('status', 50)->default('success'); // success, failed
            $table->timestamps();
            
            // Indexes for filtering
            $table->index(['admin_id', 'created_at']);
            $table->index(['resource', 'resource_id']);
            $table->index(['action', 'status']);
            
            // Foreign key
            $table->foreign('admin_id')
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
        Schema::dropIfExists('system_logs');
    }
};
