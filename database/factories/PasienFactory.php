<?php

namespace Database\Factories;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasienFactory extends Factory
{
    protected $model = Pasien::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'pasien']),
            'nik' => $this->faker->unique()->numerify('3215##########'),
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->numerify('081########'),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->numerify('081########'),
            'blood_type' => $this->faker->randomElement(['O', 'A', 'B', 'AB']),
            'allergies' => null,
        ];
    }
}