<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Story>
 */
class StoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'cover_media_id' => Media::factory(),
            'title' => [
                'en' => fake()->sentence(),
                'fr' => fake()->sentence(),
            ],
            'content' => [
                'en' => fake()->paragraph(),
                'fr' => fake()->paragraph(),
            ],
            'published_at' => fake()->dateTime(),
        ];
    }
}
