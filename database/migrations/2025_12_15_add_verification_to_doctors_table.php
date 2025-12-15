<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->boolean('is_verified')
                ->default(false)
                ->after('is_available')
                ->comment('Status verifikasi dokter oleh admin');
            
            $table->text('verification_notes')
                ->nullable()
                ->after('is_verified')
                ->comment('Catatan verifikasi dari admin');
            
            $table->timestamp('verified_at')
                ->nullable()
                ->after('verification_notes')
                ->comment('Waktu dokter diverifikasi');
            
            $table->unsignedBigInteger('verified_by_admin_id')
                ->nullable()
                ->after('verified_at')
                ->comment('ID admin yang melakukan verifikasi');
            
            // Indexes
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex(['is_verified']);
            $table->dropColumn(['is_verified', 'verification_notes', 'verified_at', 'verified_by_admin_id']);
        });
    }
};
