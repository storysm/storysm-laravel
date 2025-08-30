<?php

namespace App\Livewire\StoryCommentVote;

use App\Enums\Vote\Type;
use App\Models\StoryComment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UpvoteAction extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public StoryComment $storyComment;

    public function mount(StoryComment $storyComment): void
    {
        $this->storyComment = $storyComment;
    }

    public function upvoteAction(): Action
    {
        $active = $this->storyComment->currentUserVote()?->type === Type::Up;

        return Action::make('upvote')
            ->action(function () {
                if (! Auth::check()) {
                    Notification::make()
                        ->title(__('story-comment-vote.notification.login_required.title'))
                        ->body(__('story-comment-vote.notification.login_required.body'))
                        ->warning()
                        ->actions([
                            Notifications\Actions\Action::make('login')
                                ->label(__('story-comment-vote.notification.login_required.action.login'))
                                ->url(route('login'))
                                ->button(),
                        ])
                        ->send();

                    return;
                }

                $this->storyComment->vote(Type::Up);
                $this->storyComment->refresh();

                $this->dispatch('vote-updated', storyCommentId: $this->storyComment->id);
            })
            ->icon($active ? 'heroicon-m-hand-thumb-up' : 'heroicon-o-hand-thumb-up')
            ->label($this->storyComment->formattedUpvoteCount())
            ->outlined(! $active);
    }

    #[On('vote-updated')]
    public function refreshStoryComment(string $storyCommentId): void
    {
        if ($this->storyComment->id === $storyCommentId) {
            $this->storyComment->refresh();
        }
    }

    public function render(): View
    {
        return view('livewire.story-comment-vote.upvote-action');
    }
}
