<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tingkat = $this->faker->numberBetween(7, 9);
        $huruf = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);

        return [
            'nama_kelas' => "{$tingkat}{$huruf}",
            'tingkat' => $tingkat,
        ];
    }
}
