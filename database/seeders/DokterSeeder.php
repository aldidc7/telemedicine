<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================
 * SEEDER DOKTER - DUMMY DATA DOKTER
 * ============================================
 * 
 * Seeder ini membuat data dummy untuk tabel dokter.
 * Untuk prototipe skripsi, semua dokter adalah Dokter Anak.
 * 
 * Jalankan dengan:
 * php artisan db:seed --class=DokterSeeder
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class DokterSeeder extends Seeder
{
    public function run(): void
    {
        // ============ DATA DOKTER DUMMY ============
        $dokter_data = [
            [
                'name' => 'Dr. Setiawan Wijaya',
                'email' => 'setiawan.wijaya@rsud.go.id',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'is_active' => true,
                'last_login_at' => now(),
                'dokter' => [
                    'specialization' => 'Dokter Anak',
                    'license_number' => 'SIP-JE-2020-000001',
                    'phone_number' => '081999888777',
                    'is_available' => true,
                    'max_concurrent_consultations' => 5,
                ]
            ],
            [
                'name' => 'Dr. Sinta Nurmalasari',
                'email' => 'sinta.nurmalasari@rsud.go.id',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'is_active' => true,
                'last_login_at' => now(),
                'dokter' => [
                    'specialization' => 'Dokter Anak',
                    'license_number' => 'SIP-JE-2019-000002',
                    'phone_number' => '081888777666',
                    'is_available' => true,
                    'max_concurrent_consultations' => 5,
                ]
            ],
            [
                'name' => 'Dr. Bambang Irawan',
                'email' => 'bambang.irawan@rsud.go.id',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'is_active' => true,
                'last_login_at' => now(),
                'dokter' => [
                    'specialization' => 'Dokter Anak',
                    'license_number' => 'SIP-JE-2021-000003',
                    'phone_number' => '082777666555',
                    'is_available' => true,
                    'max_concurrent_consultations' => 5,
                ]
            ],
        ];

        // ============ LOOP & CREATE DOKTER ============
        foreach ($dokter_data as $data) {
            $dokter_info = $data['dokter'];
            unset($data['dokter']);

            // Create user terlebih dahulu
            $user = User::create($data);

            // Create dokter dengan user_id yang baru dibuat
            Dokter::create(array_merge($dokter_info, ['user_id' => $user->id]));
        }

        echo "✅ Dokter seeder completed - " . count($dokter_data) . " dokter created\n";
    }
}