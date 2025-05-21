<?php

namespace App\Livewire\StoryComment;

use App\Models\StoryComment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StoryCommentCard extends Component
{
    public StoryComment $storyComment;

    public bool $showReplies;

    public function mount(StoryComment $storyComment, bool $showReplies = true): void
    {
        $this->storyComment = $storyComment;
        $this->showReplies = $showReplies;
    }

    public function render(): View
    {
        return view('livewire.story-comment.story-comment-card');
    }
}
