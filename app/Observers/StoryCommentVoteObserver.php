<?php

namespace App\Observers;

use App\Models\StoryCommentVote;
use App\Services\StoryCommentVoteService;

class StoryCommentVoteObserver
{
    protected StoryCommentVoteService $storyCommentVoteService;

    public function __construct(StoryCommentVoteService $storyCommentVoteService)
    {
        $this->storyCommentVoteService = $storyCommentVoteService;
    }

    /**
     * Handle the StoryCommentVote "created" event.
     */
    public function created(StoryCommentVote $storyCommentVote): void
    {
        $this->storyCommentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }

    /**
     * Handle the StoryCommentVote "updated" event.
     */
    public function updated(StoryCommentVote $storyCommentVote): void
    {
        $this->storyCommentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }

    /**
     * Handle the StoryCommentVote "deleted" event.
     */
    public function deleted(StoryCommentVote $storyCommentVote): void
    {
        $this->storyCommentVoteService->recalculateVoteCounts($storyCommentVote->comment);
    }
}
