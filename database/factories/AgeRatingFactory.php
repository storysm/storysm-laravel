<?php

namespace Database\Factories;

use App\Models\AgeRating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<AgeRating>
 */
class AgeRatingFactory extends Factory
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
            'age_representation' => $this->faker->numberBetween(0, 18),
        ];
    }
}
