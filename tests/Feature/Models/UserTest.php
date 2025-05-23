<?php

namespace Tests\Feature\Models;

use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_many_voted_stories(): void
    {
        $user = User::factory()->create();
        $story1 = Story::factory()->create();
        $story2 = Story::factory()->create();
        $story3 = Story::factory()->create();

        // Create votes for the user on story1 and story3
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story1->id,
        ]);
        Vote::factory()->create([
            'creator_id' => $user->id,
            'story_id' => $story3->id,
        ]);

        // Ensure story2 is not voted on by this user
        Vote::factory()->create([
            'creator_id' => User::factory()->create()->id,
            'story_id' => $story2->id,
        ]);

        $this->assertCount(2, $user->votedStories);
        $this->assertTrue($user->votedStories->contains($story1));
        $this->assertFalse($user->votedStories->contains($story2));
        $this->assertTrue($user->votedStories->contains($story3));

        // Check the relationship type
        $this->assertInstanceOf(BelongsToMany::class, $user->votedStories());
    }

    public function test_user_has_many_votes(): void
    {
        $user = User::factory()->create();
        $votes = Vote::factory()->count(3)->for($user, 'creator')->create();

        $this->assertInstanceOf(Collection::class, $user->votes);
        $this->assertCount(3, $user->votes);
        $this->assertTrue($user->votes->contains($votes->firstOrFail()));
    }
}
