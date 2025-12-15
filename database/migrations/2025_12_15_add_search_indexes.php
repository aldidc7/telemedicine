<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support adding indexes the same way, so we'll skip
        // Indexes are already created during table creation
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback
    }
};
