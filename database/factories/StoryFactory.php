<?php

namespace Database\Factories;

use App\Enums\Story\Status;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'status' => Status::Draft,
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the story is published.
     */
    public function ensurePublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Publish,
            'published_at' => Carbon::now(),
        ]);
    }
}
