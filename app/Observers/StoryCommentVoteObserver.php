<?php

namespace App\Observers;

use App\Models\StoryCommentVote;
use App\Services\CommentVoteService;

class StoryCommentVoteObserver
{
    protected CommentVoteService $commentVoteService;

    public function __construct(CommentVoteService $commentVoteService)
    {
        $this->commentVoteService = $commentVoteService;
    }

    /**
     * Handle the StoryCommentVote "created" event.
     */
    public function created(StoryCommentVote $storyCommentVote): void
    {
        $this->commentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }

    /**
     * Handle the StoryCommentVote "updated" event.
     */
    public function updated(StoryCommentVote $storyCommentVote): void
    {
        $this->commentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }

    /**
     * Handle the StoryCommentVote "deleted" event.
     */
    public function deleted(StoryCommentVote $storyCommentVote): void
    {
        $this->commentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }
}
