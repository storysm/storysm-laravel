<?php

namespace Tests\Feature\Services;

use App\Enums\Vote\Type;
use App\Models\StoryComment;
use App\Models\StoryCommentVote;
use App\Services\StoryCommentVoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryCommentVoteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StoryCommentVoteService $storyCommentVoteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storyCommentVoteService = new StoryCommentVoteService();
    }

    /**
     * Test that recalculateVoteCounts correctly calculates and saves
     * upvote_count, downvote_count, vote_count, and vote_score for a comment
     * with a mix of upvotes and downvotes.
     */
    public function test_recalculate_vote_counts_with_mixed_votes(): void
    {
        // Create a comment
        $comment = StoryComment::factory()->create();

        // Create upvotes
        StoryCommentVote::factory()->count(5)->create([
            'story_comment_id' => $comment->id,
            'type' => Type::Up,
        ]);

        // Create downvotes
        StoryCommentVote::factory()->count(2)->create([
            'story_comment_id' => $comment->id,
            'type' => Type::Down,
        ]);

        // Recalculate vote counts
        $this->storyCommentVoteService->recalculateVoteCounts($comment);

        // Refresh the comment model to get the updated counts
        $comment->refresh();

        // Assertions
        $this->assertEquals(5, $comment->upvote_count);
        $this->assertEquals(2, $comment->downvote_count);
        $this->assertEquals(7, $comment->vote_count); // 5 up + 2 down
        $this->assertEquals(2.8, $comment->vote_score); // 5 up - (2 down * 1.1 penalty)
    }

    /**
     * Test that recalculateVoteCounts correctly handles the edge case of a
     * comment with zero votes, setting all counts to 0.
     */
    public function test_recalculate_vote_counts_with_zero_votes(): void
    {
        // Create a comment
        $comment = StoryComment::factory()->create();

        // Recalculate vote counts for a comment with no votes
        $this->storyCommentVoteService->recalculateVoteCounts($comment);

        // Refresh the comment model to get the updated counts
        $comment->refresh();

        // Assertions
        $this->assertEquals(0, $comment->upvote_count);
        $this->assertEquals(0, $comment->downvote_count);
        $this->assertEquals(0, $comment->vote_count);
        $this->assertEquals(0, $comment->vote_score);
    }
}