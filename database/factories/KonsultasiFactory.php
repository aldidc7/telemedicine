<?php

namespace Database\Factories;

use App\Models\Konsultasi;
use App\Models\Dokter;
use App\Models\Pasien;
use Illuminate\Database\Eloquent\Factories\Factory;

class KonsultasiFactory extends Factory
{
    protected $model = Konsultasi::class;

    public function definition(): array
    {
        $complaints = ['Demam', 'Batuk', 'Diare', 'Alergi', 'Infeksi Telinga', 'Pusing'];
        
        $status = $this->faker->randomElement(['pending', 'active', 'closed', 'cancelled']);
        
        $start_time = $status !== 'pending' ? $this->faker->dateTime() : null;
        $end_time = $status === 'closed' && $start_time ? 
            \Carbon\Carbon::parse($start_time)->addMinutes(rand(15, 60)) : null;

        return [
            'patient_id' => Pasien::factory(),
            'doctor_id' => Dokter::factory(),
            'complaint_type' => $this->faker->randomElement($complaints),
            'description' => $this->faker->sentence(10),
            'status' => $status,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'closing_notes' => $status === 'closed' ? $this->faker->paragraph() : null,
            'simrs_synced' => $status === 'closed' ? true : false,
            'synced_at' => $status === 'closed' ? now() : null,
        ];
    }
}