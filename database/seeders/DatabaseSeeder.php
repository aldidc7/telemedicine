<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * ============================================
 * DATABASE SEEDER - MAIN SEEDER
 * ============================================
 * 
 * Seeder utama yang menjalankan semua seeder lainnya.
 * Urutan penting: User → Pasien/Dokter/Admin → Consultations, etc
 * 
 * Jalankan dengan:
 * php artisan db:seed
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ============ SEED DALAM URUTAN YANG TEPAT ============
        
        // 1. Seed Pasien (yang include User creation)
        $this->call(PasienSeeder::class);
        echo "\n";

        // 2. Seed Dokter (yang include User creation)
        $this->call(DokterSeeder::class);
        echo "\n";

        // 3. Seed Admin (yang include User creation)
        $this->call(AdminSeeder::class);
        echo "\n";

        // 4. Seed Consultations dan related data
        $this->call(KonsultasiSeeder::class);
        echo "\n";

        $this->call(PesanChatSeeder::class);
        echo "\n";

        $this->call(RekamMedisSeeder::class);
        echo "\n";

        echo "\n✅ All seeders completed successfully!\n";
    }
}