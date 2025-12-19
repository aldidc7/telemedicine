<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make user_id nullable in activity_logs to support logging failed login attempts
     * where user_id is not available (user not authenticated yet)
     */
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                // Drop the foreign key constraint if it exists
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                // Modify user_id to be nullable
                $table->foreignId('user_id')
                    ->nullable()
                    ->change()
                    ->constrained('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                // Revert user_id back to NOT NULL
                $table->foreignId('user_id')
                    ->change()
                    ->constrained('users')
                    ->onDelete('cascade');
            });
        }
    }
};
