<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calon>
 */
class CalonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['putra', 'putri']),
            'nomor_urut' => $this->faker->numberBetween(1, 3),
            'deskripsi' => $this->faker->sentence(10),
            'foto_url' => null,
        ];
    }
}
