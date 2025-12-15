<?php

namespace App\Database;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan indexes untuk query optimization
     * Fokus pada frequently queried dan joined columns
     */
    public function up(): void
    {
        // Konsultasi table indexes
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->index('pasien_id');
            $table->index('dokter_id');
            $table->index('status');
            $table->index(['status', 'created_at']);
            $table->index('updated_at');
        });

        // PesanChat table indexes
        Schema::table('pesan_chat', function (Blueprint $table) {
            $table->index('konsultasi_id');
            $table->index('pengirim_id');
            $table->index('dibaca');
            $table->index(['konsultasi_id', 'dibaca']);
            $table->index('created_at');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('created_at');
        });

        // Pasien table indexes
        Schema::table('pasien', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('no_identitas');
        });

        // Dokter table indexes
        Schema::table('dokter', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('spesialisasi');
            $table->index('status_ketersediaan');
        });

        // Rating table indexes
        Schema::table('rating', function (Blueprint $table) {
            $table->index('konsultasi_id');
            $table->index('created_at');
        });

        // RekamMedis table indexes
        Schema::table('rekam_medis', function (Blueprint $table) {
            $table->index('konsultasi_id');
            $table->index('dokter_id');
            $table->index('created_at');
        });

        // ActivityLog table indexes
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsultasi', function (Blueprint $table) {
            $table->dropIndex(['pasien_id']);
            $table->dropIndex(['dokter_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('pesan_chat', function (Blueprint $table) {
            $table->dropIndex(['konsultasi_id']);
            $table->dropIndex(['pengirim_id']);
            $table->dropIndex(['dibaca']);
            $table->dropIndex(['konsultasi_id', 'dibaca']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('pasien', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['no_identitas']);
        });

        Schema::table('dokter', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['spesialisasi']);
            $table->dropIndex(['status_ketersediaan']);
        });

        Schema::table('rating', function (Blueprint $table) {
            $table->dropIndex(['konsultasi_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('rekam_medis', function (Blueprint $table) {
            $table->dropIndex(['konsultasi_id']);
            $table->dropIndex(['dokter_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['model_type']);
            $table->dropIndex(['model_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
