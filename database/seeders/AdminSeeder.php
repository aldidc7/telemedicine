<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================
 * SEEDER ADMIN - DUMMY DATA ADMIN
 * ============================================
 * 
 * Seeder ini membuat data dummy untuk tabel admin.
 * Membuat beberapa admin dengan permission level berbeda.
 * 
 * Jalankan dengan:
 * php artisan db:seed --class=AdminSeeder
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ============ DATA ADMIN DUMMY ============
        $admin_data = [
            [
                'name' => 'Admin Telemedicine',
                'email' => 'admintelemedicine@admin.local',
                'password' => Hash::make('Rsud123!'),
                'role' => 'admin',
                'is_active' => true,
                'last_login_at' => now(),
                'admin' => [
                    'permission_level' => 3, // Super Admin
                    'notes' => 'Super admin dengan akses penuh',
                ]
            ],
        ];

        // ============ LOOP & CREATE ADMIN ============
        foreach ($admin_data as $data) {
            $admin_info = $data['admin'];
            unset($data['admin']);

            // Create user terlebih dahulu
            $user = User::create($data);

            // Create admin dengan user_id yang baru dibuat
            Admin::create(array_merge($admin_info, ['user_id' => $user->id]));
        }

        echo "✅ Admin seeder completed - " . count($admin_data) . " admin created\n";
    }
}