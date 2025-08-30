<?php

namespace App\Services;

use App\Constants\VotingConstants;
use App\Enums\Vote\Type;
use App\Models\StoryComment;

class CommentVoteService
{
    /**
     * Recalculates and updates the denormalized vote counts for a given StoryComment.
     */
    public function recalculateVoteCounts(StoryComment $storyComment): void
    {
        $upvotes = $storyComment->votes()->where('type', Type::Up)->count();
        $downvotes = $storyComment->votes()->where('type', Type::Down)->count();

        $storyComment->upvote_count = $upvotes;
        $storyComment->downvote_count = $downvotes;
        $storyComment->vote_count = $upvotes + $downvotes;
        $storyComment->vote_score = $upvotes - ($downvotes * VotingConstants::DOWNVOTE_PENALTY_WEIGHT);
        $storyComment->save();
    }
}
