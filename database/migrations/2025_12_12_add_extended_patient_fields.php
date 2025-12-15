<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('patients', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('patients', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            }
            if (!Schema::hasColumn('patients', 'religion')) {
                $table->string('religion')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('patients', 'ethnicity')) {
                $table->string('ethnicity')->nullable()->after('religion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumnIfExists('place_of_birth');
            $table->dropColumnIfExists('marital_status');
            $table->dropColumnIfExists('religion');
            $table->dropColumnIfExists('ethnicity');
        });
    }
};
