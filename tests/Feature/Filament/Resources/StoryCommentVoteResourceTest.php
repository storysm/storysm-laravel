<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\Vote\Type;
use App\Filament\Resources\StoryCommentVoteResource;
use App\Filament\Resources\StoryCommentVoteResource\Pages\ListStoryCommentVotes;
use App\Models\Permission;
use App\Models\StoryComment;
use App\Models\StoryCommentVote;
use App\Models\User; // Added for the new test case
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class StoryCommentVoteResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_comment_vote_resource_index_page_renders(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(StoryCommentVoteResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_users_without_view_all_permission_only_see_their_own_story_comment_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();

        // Create story comment votes
        $storyCommentVote1_user1 = StoryCommentVote::factory()->create(['creator_id' => $user1->id]);
        $storyCommentVote2_user1 = StoryCommentVote::factory()->create(['creator_id' => $user1->id]);
        $storyCommentVote1_user2 = StoryCommentVote::factory()->create(['creator_id' => $user2->id]);
        $storyCommentVote2_user2 = StoryCommentVote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListStoryCommentVotes::class);

        /** @var ListStoryCommentVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, StoryCommentVote> */
        $records = $table->getRecords();

        // Assert that user1 only sees their own story comment votes
        $this->assertEquals(2, $records->count());
        $this->assertTrue($records->contains($storyCommentVote1_user1));
        $this->assertTrue($records->contains($storyCommentVote2_user1));
        $this->assertFalse($records->contains($storyCommentVote1_user2));
        $this->assertFalse($records->contains($storyCommentVote2_user2));
    }

    public function test_users_with_view_all_permission_see_all_story_comment_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_story::comment::vote']);
        $user1->givePermissionTo('view_all_story::comment::vote');

        /** @var User */
        $user2 = User::factory()->create();

        // Create story comment votes
        $storyCommentVote1_user1 = StoryCommentVote::factory()->create(['creator_id' => $user1->id]);
        $storyCommentVote2_user1 = StoryCommentVote::factory()->create(['creator_id' => $user1->id]);
        $storyCommentVote1_user2 = StoryCommentVote::factory()->create(['creator_id' => $user2->id]);
        $storyCommentVote2_user2 = StoryCommentVote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListStoryCommentVotes::class);

        /** @var ListStoryCommentVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, StoryCommentVote> */
        $records = $table->getRecords();

        // Assert that user1 sees all story comment votes
        $this->assertCount(4, $records);
        $this->assertTrue($records->contains($storyCommentVote1_user1));
        $this->assertTrue($records->contains($storyCommentVote2_user1));
        $this->assertTrue($records->contains($storyCommentVote1_user2));
        $this->assertTrue($records->contains($storyCommentVote2_user2));
    }

    public function test_story_comment_vote_resource_index_page_filters_by_type(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create story comment votes of different types
        $upvote1 = StoryCommentVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Up]);
        $upvote2 = StoryCommentVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Up]);
        $downvote1 = StoryCommentVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Down]);
        $downvote2 = StoryCommentVote::factory()->create(['creator_id' => $user->id, 'type' => Type::Down]);

        $testable = Livewire::test(ListStoryCommentVotes::class);
        $testable->assertCanSeeTableRecords([$upvote1, $upvote2, $downvote1, $downvote2]); // See all initially
        $testable->filterTable('type', Type::Up->value);
        $testable->assertCanSeeTableRecords([$upvote1, $upvote2]);
        $testable->assertCanNotSeeTableRecords([$downvote1, $downvote2]);
        $testable->filterTable('type', Type::Down->value); // Apply DOWNVOTE filter
        $testable->assertCanSeeTableRecords([$downvote1, $downvote2]);
        $testable->assertCanNotSeeTableRecords([$upvote1, $upvote2]);
    }

    public function test_view_action_links_to_associated_comment_public_page(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        /** @var StoryComment */
        $storyComment = StoryComment::factory()->create(); // Create the comment first
        /** @var StoryCommentVote */
        $storyCommentVote = StoryCommentVote::factory()->create([
            'creator_id' => $user->id,
            'story_comment_id' => $storyComment->id, // Associate the vote with the created comment
        ]);

        $testable = Livewire::test(ListStoryCommentVotes::class);
        $testable->assertTableActionHasUrl(
            'view',
            route('story-comments.show', $storyComment->id),
            $storyCommentVote,
        );
    }
}
