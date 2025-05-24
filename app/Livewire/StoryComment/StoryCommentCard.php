<?php

namespace App\Livewire\StoryComment;

use App\Filament\Resources\StoryCommentResource;
use App\Models\StoryComment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
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

    public bool $showReplyButton;

    public bool $hasUserReplied;

    public bool $showActions;

    public function mount(StoryComment $storyComment, bool $showReplyButton = true, bool $showActions = true): void
    {
        $this->storyComment = $storyComment;
        $this->showReplyButton = $showReplyButton;
        $this->hasUserReplied = false;
        $this->showActions = $showActions;

        if (Auth::check()) {
            $this->hasUserReplied = $storyComment->storyComments()->where('creator_id', Auth::id())->exists();
        }
    }

    protected function deleteAction(): Action
    {
        return DeleteAction::make()
            ->after(fn () => $this->dispatch('storyCommentDeleted'))
            ->authorize($this->deleteActionPermitted())
            ->record($this->storyComment);
    }

    public function deleteActionPermitted(): bool
    {
        return StoryCommentResource::canDelete($this->storyComment);
    }

    public function editAction(): Action
    {
        return EditAction::make()
            ->authorize($this->editActionPermitted())
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
