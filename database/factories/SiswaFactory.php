<?php

namespace Database\Factories;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nis' => $this->faker->unique()->numerify('########'),
            'kelas_id' => Kelas::factory(),
            'tahun' => date('Y'),
            'tingkat' => $this->faker->numberBetween(7, 9),
            'pindahan' => $this->faker->boolean(),
        ];
    }
}
