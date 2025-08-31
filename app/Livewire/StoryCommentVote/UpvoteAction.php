<?php

namespace App\Livewire\StoryCommentVote;

use App\Enums\Vote\Type;
use Filament\Actions\Action;

class UpvoteAction extends VoteAction
{
    protected function getVoteType(): Type
    {
        return Type::Up;
    }

    protected function getActionName(): string
    {
        return 'upvote';
    }

    protected function getIcon(bool $active): string
    {
        return $active ? 'heroicon-m-hand-thumb-up' : 'heroicon-o-hand-thumb-up';
    }

    protected function getFormattedCount(): string
    {
        return $this->storyComment->formattedUpvoteCount();
    }

    protected function getActionColor(): ?string
    {
        return null;
    }

    protected function getViewName(): string
    {
        return 'livewire.story-comment-vote.upvote-action';
    }

    public function upvoteAction(): Action
    {
        return $this->voteAction();
    }
}
