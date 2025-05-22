<?php

namespace App\Livewire\StoryComment;

use App\Filament\Resources\StoryCommentResource;
use App\Models\StoryComment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property Action $deleteAction
 */
class ViewStoryComment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public StoryComment $storyComment;

    public function mount(StoryComment $storyComment): void
    {
        $this->storyComment = $storyComment;
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        $actions = [];
        $actions[] = Action::make('edit')
            ->authorize(StoryCommentResource::canEdit($this->storyComment))
            ->label(__('Edit'))
            ->url(route('filament.admin.resources.story-comments.edit', $this->storyComment));
        $actions[] = $this->deleteAction;

        return $actions;
    }

    protected function deleteAction(): Action
    {
        $parent = $this->storyComment->parent;
        $story = $this->storyComment->story;

        return DeleteAction::make()
            ->color('danger')
            ->record($this->storyComment)
            ->successRedirectUrl(fn () => $parent
                ? route('story-comments.show', ['storyComment' => $parent])
                : route('stories.show', $story));
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            route('home') => __('navigation-menu.menu.home'),
            route('stories.index') => trans_choice('story.resource.model_label', 2),
            route('stories.show', $this->storyComment->story) => Str::limit($this->storyComment->story->title, 50),
            0 => trans_choice('story-comment.resource.model_label', 2),
            1 => __('View'),
        ];

        return $breadcrumbs;
    }

    public function render(): View
    {
        return view('livewire.story-comment.view-story-comment');
    }
}
