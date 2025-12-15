<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================
 * SEEDER PASIEN - DUMMY DATA PASIEN
 * ============================================
 * 
 * Seeder ini membuat data dummy untuk tabel pasien.
 * Menciptakan beberapa pasien beserta user accountnya.
 * 
 * Jalankan dengan:
 * php artisan db:seed --class=PasienSeeder
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class PasienSeeder extends Seeder
{
    public function run(): void
    {
        // ============ DATA PASIEN DUMMY ============
        $pasien_data = [
            [
                'name' => 'Ahmad Zaki',
                'email' => 'ahmad.zaki@email.com',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
                'pasien' => [
                    'nik' => '3215001234567890',
                    'date_of_birth' => '2015-06-15',
                    'gender' => 'laki-laki',
                    'address' => 'Jl. Raya Pasuruan No. 123',
                    'phone_number' => '081234567890',
                    'emergency_contact_name' => 'Budi Zaki',
                    'emergency_contact_phone' => '082345678901',
                    'blood_type' => 'O',
                    'allergies' => 'Tidak Ada',
                ]
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@email.com',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
                'pasien' => [
                    'nik' => '3215002345678901',
                    'date_of_birth' => '2016-03-20',
                    'gender' => 'perempuan',
                    'address' => 'Jl. Diponegoro No. 45',
                    'phone_number' => '082345678901',
                    'emergency_contact_name' => 'Rina Aminah',
                    'emergency_contact_phone' => '083456789012',
                    'blood_type' => 'A',
                    'allergies' => 'Telur',
                ]
            ],
            [
                'name' => 'Raka Pratama',
                'email' => 'raka.pratama@email.com',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
                'pasien' => [
                    'nik' => '3215003456789012',
                    'date_of_birth' => '2014-11-08',
                    'gender' => 'laki-laki',
                    'address' => 'Jl. Ahmad Yani No. 78',
                    'phone_number' => '083456789012',
                    'emergency_contact_name' => 'Doni Pratama',
                    'emergency_contact_phone' => '084567890123',
                    'blood_type' => 'B',
                    'allergies' => 'Penisilin',
                ]
            ],
            [
                'name' => 'Nur Hidayah',
                'email' => 'nur.hidayah@email.com',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
                'pasien' => [
                    'nik' => '3215004567890123',
                    'date_of_birth' => '2017-01-12',
                    'gender' => 'perempuan',
                    'address' => 'Jl. Gatot Subroto No. 56',
                    'phone_number' => '085678901234',
                    'emergency_contact_name' => 'Hasan Hidayah',
                    'emergency_contact_phone' => '086789012345',
                    'blood_type' => 'AB',
                    'allergies' => 'Tidak Ada',
                ]
            ],
        ];

        // ============ LOOP & CREATE PASIEN ============
        foreach ($pasien_data as $data) {
            $pasien_info = $data['pasien'];
            unset($data['pasien']);

            // Create user terlebih dahulu
            $user = User::create($data);

            // Create pasien dengan user_id yang baru dibuat
            Pasien::create(array_merge($pasien_info, ['user_id' => $user->id]));
        }

        echo "✅ Pasien seeder completed - " . count($pasien_data) . " pasien created\n";
    }
}