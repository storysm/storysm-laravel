<?php

namespace Database\Factories;

use App\Models\StoryComment;
use App\Models\StoryCommentVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<StoryCommentVote>
 */
class StoryCommentVoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\StoryCommentVote>
     */
    protected $model = StoryCommentVote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'story_comment_id' => StoryComment::factory(),
            'type' => $this->faker->randomElement(['upvote', 'downvote']),
        ];
    }
}
