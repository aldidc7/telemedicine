<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify existing users table
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->enum('role', ['pasien', 'dokter', 'admin'])
                ->default('pasien')
                ->after('password');
            
            $table->boolean('is_active')
                ->default(true)
                ->after('role');
            
            $table->timestamp('last_login_at')
                ->nullable()
                ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};