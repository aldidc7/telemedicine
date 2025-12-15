<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Profile fields
            $table->string('profile_photo')->nullable()->comment('Foto profil dokter');
            $table->text('address')->nullable()->comment('Alamat domisili');
            $table->date('place_of_birth')->nullable()->comment('Tempat lahir (sebagai tanggal untuk sekarang)');
            $table->string('birthplace_city')->nullable()->comment('Nama kota tempat lahir');
            $table->enum('marital_status', ['belum_menikah', 'menikah', 'cerai', 'cerai_mati'])->nullable()->comment('Status pernikahan');
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable();
            $table->enum('blood_type', ['O', 'A', 'B', 'AB'])->nullable()->comment('Golongan darah');
            $table->string('ethnicity')->nullable()->comment('Suku');
            
            // Track if synced with patient
            $table->boolean('patient_synced')->default(false)->comment('Apakah sudah membuat profil pasien');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo',
                'address',
                'place_of_birth',
                'birthplace_city',
                'marital_status',
                'gender',
                'blood_type',
                'ethnicity',
                'patient_synced',
            ]);
        });
    }
};
