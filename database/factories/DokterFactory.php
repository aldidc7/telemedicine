<?php

namespace Database\Factories;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DokterFactory extends Factory
{
    protected $model = Dokter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'dokter']),
            'specialization' => $this->faker->randomElement(['Dokter Anak', 'Dokter Umum', 'Kardiologi', 'Neurologi', 'Oftalmologi']),
            'license_number' => $this->faker->unique()->numerify('SIP-########'),
            'phone_number' => $this->faker->numerify('081########'),
            'is_available' => true,
            'max_concurrent_consultations' => 5,
        ];
    }

    public function available(): self
    {
        return $this->state([
            'is_available' => true,
        ]);
    }

    public function unavailable(): self
    {
        return $this->state([
            'is_available' => false,
        ]);
    }
}