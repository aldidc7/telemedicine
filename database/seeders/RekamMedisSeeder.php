<?php

namespace Database\Seeders;

use App\Models\RekamMedis;
use App\Models\Pasien;
use Illuminate\Database\Seeder;

class RekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        $pasiens = Pasien::all();

        if ($pasiens->isEmpty()) {
            $this->command->info('Pasien belum ada. Jalankan PasienSeeder terlebih dahulu.');
            return;
        }

        $diagnosises = [
            'Demam akibat infeksi virus',
            'Otitis media akut',
            'Faringitis akut',
            'Pneumonia komunitas',
            'Gastroenteritis akut',
            'Dermatitis atopik',
            'Alergi makanan',
            'Asma bronkial',
            'Anemia defisiensi besi',
            'Malnutrisi ringan',
        ];

        $procedures = [
            'Pemeriksaan fisik lengkap',
            'Pemeriksaan darah lengkap',
            'Pemeriksaan urine rutin',
            'Rontgen dada',
            'Tes alergi',
            'EKG',
            'USG abdomen',
            'Konsultasi gizi',
        ];

        $prescriptions = [
            'Paracetamol 500mg 3x sehari',
            'Amoxicillin 250mg 3x sehari',
            'Cetirizine 10mg 1x malam',
            'Omeprazole 20mg 2x sehari',
            'Vitamin C 500mg 2x sehari',
            'Salbutamol inhaler 2x sehari',
            'Loratadine 10mg 1x malam',
            'Ibuprofen 400mg 3x sehari',
        ];

        $rekamMedis = [];
        $rekamMedisCount = 0;

        foreach ($pasiens as $pasien) {
            // Setiap pasien punya 2-4 rekam medis
            $jumlahRekam = rand(2, 4);

            for ($i = 0; $i < $jumlahRekam; $i++) {
                $recordDate = now()->subDays(rand(1, 180));
                $rekamMedis[] = [
                    'patient_id' => $pasien->id,
                    'record_type' => 'diagnosis',
                    'data' => json_encode([
                        'diagnosis' => $diagnosises[array_rand($diagnosises)],
                        'procedure' => $procedures[array_rand($procedures)],
                        'prescription' => $prescriptions[array_rand($prescriptions)],
                    ]),
                    'record_date' => $recordDate->format('Y-m-d'),
                    'source' => rand(0, 1) ? 'SIMRS' : 'LOCAL',
                    'notes' => 'Pasien dalam kondisi ' . ['stabil', 'baik', 'membaik', 'terkontrol'][rand(0, 3)],
                    'created_at' => $recordDate,
                    'updated_at' => $recordDate,
                ];
                $rekamMedisCount++;
            }
        }

        foreach (array_chunk($rekamMedis, 10) as $chunk) {
            RekamMedis::insert($chunk);
        }

        $this->command->info("✅ Berhasil buat $rekamMedisCount dummy rekam medis!");
    }
}
