<?php

namespace App\Livewire\StoryComment;

use App\Filament\Resources\StoryCommentResource;
use App\Models\StoryComment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StoryCommentCard extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

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

    protected function editAction(): Action
    {
        return EditAction::make()
            ->url(route('filament.admin.resources.story-comments.edit', $this->storyComment));
    }

    public function editActionPermitted(): bool
    {
        return StoryCommentResource::canEdit($this->storyComment);
    }

    public function render(): View
    {
        return view('livewire.story-comment.story-comment-card');
    }
}
