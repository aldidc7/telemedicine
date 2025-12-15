<?php

namespace Database\Factories;

use App\Models\PesanChat;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesanChatFactory extends Factory
{
    protected $model = PesanChat::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Konsultasi::factory(),
            'sender_id' => User::factory(),
            'message' => $this->faker->sentence(),
            'message_type' => $this->faker->randomElement(['text', 'image', 'file']),
            'file_url' => $this->faker->optional(0.3)->url(),
            'read_at' => $this->faker->optional(0.7)->dateTime(),
        ];
    }
}
