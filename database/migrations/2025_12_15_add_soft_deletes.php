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
        // Add soft deletes to appointments table
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'deleted_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to messages table
        if (Schema::hasTable('messages') && !Schema::hasColumn('messages', 'deleted_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to prescriptions table
        if (Schema::hasTable('prescriptions') && !Schema::hasColumn('prescriptions', 'deleted_at')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from appointments table
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'deleted_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from messages table
        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'deleted_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from prescriptions table
        if (Schema::hasTable('prescriptions') && Schema::hasColumn('prescriptions', 'deleted_at')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
