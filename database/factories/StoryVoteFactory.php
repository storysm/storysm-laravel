<?php

namespace Database\Factories;

use App\Enums\StoryVote\Type;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoryVote>
 */
class StoryVoteFactory extends Factory
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
            'type' => Type::Up,
        ];
    }
}
