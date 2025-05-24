<?php

namespace Database\Factories;

use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoryComment>
 */
class StoryCommentFactory extends Factory
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
            'story_id' => Story::factory(),
            'body' => [
                'en' => $this->faker->sentence(),
                'id' => $this->faker->sentence(),
            ],
            'parent_id' => null,
            'reply_count' => 0,
        ];
    }
}
