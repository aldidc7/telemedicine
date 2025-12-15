<?php

namespace Database\Seeders;

use App\Models\PesanChat;
use App\Models\Konsultasi;
use Illuminate\Database\Seeder;

class PesanChatSeeder extends Seeder
{
    public function run(): void
    {
        $konsultasis = Konsultasi::with(['pasien', 'dokter'])->get();

        if ($konsultasis->isEmpty()) {
            $this->command->info('Konsultasi belum ada. Jalankan KonsultasiSeeder terlebih dahulu.');
            return;
        }

        $pesansPasien = [
            'Selamat pagi dok, anak saya demam tinggi sudah 3 hari',
            'Sudah minum obat paracetamol tapi masih panas',
            'Nafsu makan juga berkurang, apa ada yang serius?',
            'Berapa lama perlu pengobatan?',
            'Bisakah diresepkan obat yang lebih kuat?',
            'Terima kasih atas bantuan dokter',
            'Anak sudah mulai membaik hari ini',
        ];

        $pesansDokter = [
            'Selamat pagi, baik saya lihat riwayat kesehatannya',
            'Berdasarkan gejala, kemungkinan infeksi virus minor',
            'Saya resepkan Paracetamol 500mg 3x sehari',
            'Pastikan anak istirahat cukup dan banyak minum air putih',
            'Pantau suhu tubuh selama 7 hari ke depan',
            'Jika demam terus berlanjut, segera ke klinik terdekat',
            'Sama-sama, semoga cepat sembuh ya',
        ];

        $pesans = [];
        $pesanCount = 0;

        foreach ($konsultasis as $konsultasi) {
            // Skip konsultasi yang status cancelled atau pending
            if (in_array($konsultasi->status, ['cancelled', 'pending'])) {
                continue;
            }

            // Variasi jumlah pesan antara 3-7 per konsultasi
            $jumlahPesan = rand(3, 7);

            for ($i = 0; $i < $jumlahPesan; $i++) {
                $isPasienSending = $i % 2 == 0;

                $pesans[] = [
                    'consultation_id' => $konsultasi->id,
                    'sender_id' => $isPasienSending ? $konsultasi->pasien->user_id : $konsultasi->dokter->user_id,
                    'message' => $isPasienSending
                        ? $pesansPasien[array_rand($pesansPasien)]
                        : $pesansDokter[array_rand($pesansDokter)],
                    'message_type' => 'text',
                    'read_at' => rand(0, 1) ? now()->subHours(rand(1, 48)) : null,
                    'created_at' => now()->subHours(rand(1, 72)),
                ];
                $pesanCount++;
            }
        }

        foreach (array_chunk($pesans, 10) as $chunk) {
            PesanChat::insert($chunk);
        }

        $this->command->info("✅ Berhasil buat $pesanCount dummy pesan chat!");
    }
}
