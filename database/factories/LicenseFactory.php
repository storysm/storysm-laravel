<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->unique()->sentence(2, true),
                'es' => $this->faker->unique()->sentence(2, true),
            ],
            'description' => [
                'en' => $this->faker->paragraph(1),
                'es' => $this->faker->paragraph(1),
            ],
        ];
    }
}
