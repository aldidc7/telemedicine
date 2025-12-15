<?php

namespace Database\Seeders;

use App\Models\Konsultasi;
use App\Models\Pasien;
use App\Models\Dokter;
use Illuminate\Database\Seeder;

class KonsultasiSeeder extends Seeder
{
    public function run(): void
    {
        $pasiens = Pasien::all();
        $dokters = Dokter::all();

        if ($pasiens->isEmpty() || $dokters->isEmpty()) {
            $this->command->info('Pasien atau Dokter belum ada. Jalankan PasienSeeder dan DokterSeeder terlebih dahulu.');
            return;
        }

        $statuses = ['pending', 'active', 'closed', 'cancelled'];
        $complaints = [
            'Demam tinggi sudah 3 hari',
            'Batuk pilek berkepanjangan',
            'Sakit perut yang sering terasa',
            'Ruam kulit di seluruh tubuh',
            'Diare dan muntah',
            'Sesak napas ringan',
            'Kepala pusing terus',
            'Nyeri telinga saat menelan',
            'Pembengkakan di leher',
            'Alergi makanan dicurigai',
        ];

        $konsultasis = [];
        $konsultasiCount = 15;

        for ($i = 0; $i < $konsultasiCount; $i++) {
            $status = $statuses[array_rand($statuses)];
            $konsultasis[] = [
                'patient_id' => $pasiens->random()->id,
                'doctor_id' => $dokters->random()->id,
                'complaint_type' => $complaints[array_rand($complaints)],
                'description' => $complaints[array_rand($complaints)],
                'status' => $status,
                'closing_notes' => ($status === 'closed') ? 'Konsultasi selesai, pasien disarankan untuk follow-up' : null,
                'simrs_synced' => rand(0, 1) === 1,
                'synced_at' => rand(0, 1) === 1 ? now()->subDays(rand(1, 10)) : null,
                'start_time' => ($status !== 'pending') ? now()->subDays(rand(1, 20))->addHours(rand(1, 12)) : null,
                'end_time' => ($status === 'closed') ? now()->subDays(rand(1, 15))->addHours(rand(1, 12)) : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ];
        }

        foreach (array_chunk($konsultasis, 5) as $chunk) {
            Konsultasi::insert($chunk);
        }

        $this->command->info("✅ Berhasil buat $konsultasiCount dummy konsultasi!");
    }
}
