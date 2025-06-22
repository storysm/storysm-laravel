<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\StoryVote\Type;
use App\Filament\Resources\StoryVoteResource;
use App\Filament\Resources\StoryVoteResource\Pages\ListStoryVotes;
use App\Models\Permission;
use App\Models\StoryVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class StoryVoteResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_vote_resource_index_page_renders(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(StoryVoteResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_users_without_view_all_permission_only_see_their_own_story_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();

        // Create story votes
        $storyVote1_user1 = StoryVote::factory()->create(['creator_id' => $user1->id]);
        $storyVote2_user1 = StoryVote::factory()->create(['creator_id' => $user1->id]);
        $storyVote1_user2 = StoryVote::factory()->create(['creator_id' => $user2->id]);
        $storyVote2_user2 = StoryVote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListStoryVotes::class);

        /** @var ListStoryVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, StoryVote> */
        $records = $table->getRecords();

        // Assert that user1 only sees their own story votes
        $this->assertEquals(2, $records->count());
        $this->assertTrue($records->contains($storyVote1_user1));
        $this->assertTrue($records->contains($storyVote2_user1));
        $this->assertFalse($records->contains($storyVote1_user2));
        $this->assertFalse($records->contains($storyVote2_user2));
    }

    public function test_users_with_view_all_permission_see_all_story_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story_vote']);
        $user1->givePermissionTo('view_all_story_vote');

        /** @var User */
        $user2 = User::factory()->create();

        // Create story votes
        $storyVote1_user1 = StoryVote::factory()->create(['creator_id' => $user1->id]);
        $storyVote2_user1 = StoryVote::factory()->create(['creator_id' => $user1->id]);
        $storyVote1_user2 = StoryVote::factory()->create(['creator_id' => $user2->id]);
        $storyVote2_user2 = StoryVote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListStoryVotes::class);

        /** @var ListStoryVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, StoryVote> */
        $records = $table->getRecords();

        // Assert that user1 sees all story votes
        $this->assertCount(4, $records);
        $this->assertTrue($records->contains($storyVote1_user1));
        $this->assertTrue($records->contains($storyVote2_user1));
        $this->assertTrue($records->contains($storyVote1_user2));
        $this->assertTrue($records->contains($storyVote2_user2));
    }

    public function test_story_vote_resource_index_page_filters_by_type(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create story votes of different types
        $upvote1 = StoryVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Up]);
        $upvote2 = StoryVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Up]);
        $downvote1 = StoryVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Down]);
        $downvote2 = StoryVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Down]);

        $testable = Livewire::test(ListStoryVotes::class);
        $testable->assertCanSeeTableRecords([$upvote1, $upvote2, $downvote1, $downvote2]); // See all initially
        $testable->filterTable('type', Type::Up->value);
        $testable->assertCanSeeTableRecords([$upvote1, $upvote2]);
        $testable->assertCanNotSeeTableRecords([$downvote1, $downvote2]);
        $testable->filterTable('type', Type::Down->value); // Apply DOWNVOTE filter
        $testable->assertCanSeeTableRecords([$downvote1, $downvote2]);
        $testable->assertCanNotSeeTableRecords([$upvote1, $upvote2]);
    }
}
