<?php

namespace App\Livewire\StoryCommentVote;

use App\Enums\Vote\Type;
use Filament\Actions\Action;

class DownvoteAction extends VoteAction
{
    protected function getVoteType(): Type
    {
        return Type::Down;
    }

    protected function getActionName(): string
    {
        return 'downvote';
    }

    protected function getIcon(bool $active): string
    {
        return $active ? 'heroicon-m-hand-thumb-down' : 'heroicon-o-hand-thumb-down';
    }

    protected function getFormattedCount(): string
    {
        return $this->storyComment->formattedDownvoteCount();
    }

    protected function getActionColor(): ?string
    {
        return 'danger';
    }

    protected function getViewName(): string
    {
        return 'livewire.story-comment-vote.downvote-action';
    }

    public function downvoteAction(): Action
    {
        return $this->voteAction();
    }
}
