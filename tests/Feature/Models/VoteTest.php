<?php

namespace Tests\Feature\Models;

use App\Enums\Vote\Type;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_can_be_created_using_factory(): void
    {
        $vote = Vote::factory()->create();

        $this->assertDatabaseHas('votes', [
            'id' => $vote->id,
            'type' => $vote->type,
            'story_id' => $vote->story_id,
            'creator_id' => $vote->creator_id,
        ]);
        $this->assertInstanceOf(Vote::class, $vote);
    }

    public function test_vote_type_attribute_is_cast_to_enum(): void
    {
        $vote = Vote::factory()->create(['type' => Type::Up]);

        $this->assertInstanceOf(Type::class, $vote->type);
        $this->assertEquals(Type::Up, $vote->type);

        $vote = Vote::factory()->create(['type' => Type::Down]);
        $this->assertEquals(Type::Down, $vote->type);
    }

    public function test_vote_belongs_to_story(): void
    {
        $story = Story::factory()->create();
        $vote = Vote::factory()->for($story)->create();

        $this->assertInstanceOf(Story::class, $vote->story);
        $this->assertEquals($story->id, $vote->story->id);
    }

    public function test_vote_belongs_to_creator(): void
    {
        $creator = User::factory()->create();
        $vote = Vote::factory()->for($creator, 'creator')->create();

        $this->assertInstanceOf(User::class, $vote->creator);
        $this->assertEquals($creator->id, $vote->creator->id);
    }
}
