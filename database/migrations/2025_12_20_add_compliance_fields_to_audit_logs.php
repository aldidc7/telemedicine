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
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                // Add resource_type and resource_id columns if they don't exist
                if (!Schema::hasColumn('audit_logs', 'resource_type')) {
                    $table->string('resource_type')->nullable()->after('action');
                }

                if (!Schema::hasColumn('audit_logs', 'resource_id')) {
                    $table->unsignedBigInteger('resource_id')->nullable()->after('resource_type');
                }

                // Add details column if it doesn't exist
                if (!Schema::hasColumn('audit_logs', 'details')) {
                    $table->json('details')->nullable()->after('description');
                }

                // Add indexes (skip hasIndexName check for SQLite compatibility)
                if (!Schema::hasColumn('audit_logs', 'resource_type') === false) {
                    $table->index('resource_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'resource_type')) {
                    $table->dropColumn('resource_type');
                }

                if (Schema::hasColumn('audit_logs', 'resource_id')) {
                    $table->dropColumn('resource_id');
                }

                if (Schema::hasColumn('audit_logs', 'details')) {
                    $table->dropColumn('details');
                }
            });
        }
    }
};
