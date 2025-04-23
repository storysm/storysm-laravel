<?php

namespace Tests\Feature\Models;

use App\Enums\Vote\Type;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        Mockery::close(); // Close Mockery after each test
        parent::tearDown();
    }

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

    /**
     * Test that Vote model events trigger Story updateVoteCountsAndScore.
     */
    public function test_vote_events_trigger_story_update(): void
    {
        // Create a real story and user first
        $story = Story::factory()->create();
        $user = User::factory()->create();

        // Mock the specific story instance and make it partial so original methods can still be called
        // We expect updateVoteCountsAndScore to be called 3 times:
        // 1. When the vote is created (saved event)
        // 2. When the vote is updated (saved event)
        // 3. When the vote is deleted (deleted event)
        $storyMock = Mockery::mock($story)->makePartial();
        /** @var Mockery\Expectation */
        $expectation = $storyMock->shouldReceive('updateVoteCountsAndScore');
        $expectation->times(3);

        // Create a vote instance, linking it to the real user and the mocked story instance
        // We need to manually set the story relationship property to the mock
        /** @var Vote */
        $vote = Vote::factory()->for($user, 'creator')->make(['type' => Type::Up]);
        // Manually set the story_id to link to the real story in the database
        $vote->story_id = $story->id;
        $vote->setRelation('story', $storyMock); // Set the loaded relationship to the mock instance

        $vote->save(); // Trigger saved event (1st call)
        $vote->type = Type::Down;
        $vote->save(); // Trigger saved event (2nd call)
        $vote->delete(); // Trigger deleted event (3rd call)

        // Add an assertion to satisfy PHPUnit
        $this->assertDatabaseMissing('votes', ['id' => $vote->id]);
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
