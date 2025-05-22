<?php

namespace App\Livewire\StoryComment;

use App\Models\StoryComment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StoryCommentCard extends Component
{
    public StoryComment $storyComment;

    public bool $showReplies;

    public bool $hasUserReplied;

    public function mount(StoryComment $storyComment, bool $showReplies = true): void
    {
        $this->storyComment = $storyComment;
        $this->showReplies = $showReplies;
        $this->hasUserReplied = false;

        if (Auth::check()) {
            $this->hasUserReplied = $storyComment->storyComments()->where('creator_id', Auth::id())->exists();
        }
    }

    public function render(): View
    {
        return view('livewire.story-comment.story-comment-card');
    }
}
