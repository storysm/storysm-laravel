<?php

namespace App\Livewire\Comment;

use App\Filament\Resources\CommentResource;
use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class ViewComment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Comment $comment;

    public function mount(Comment $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        $actions = [];
        $actions[] = Action::make('edit')
            ->authorize(CommentResource::canEdit($this->comment))
            ->label(__('Edit'))
            ->url(route('filament.admin.resources.comments.edit', $this->comment));

        return $actions;
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            route('home') => __('navigation-menu.menu.home'),
            route('stories.index') => trans_choice('story.resource.model_label', 2),
            route('stories.show', $this->comment->story) => Str::limit($this->comment->story->title, 50),
            0 => trans_choice('comment.resource.model_label', 2),
            1 => __('View'),
        ];

        return $breadcrumbs;
    }

    public function render(): View
    {
        return view('livewire.comment.view-comment');
    }
}
