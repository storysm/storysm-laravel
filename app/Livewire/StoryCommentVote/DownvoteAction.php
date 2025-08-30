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
use Filament\Support\Enums\ActionSize;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class DownvoteAction extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public StoryComment $storyComment;

    public function mount(StoryComment $storyComment): void
    {
        $this->storyComment = $storyComment;
    }

    public function downvoteAction(): Action
    {
        $active = $this->storyComment->currentUserVote()?->type === Type::Down;

        return Action::make('downvote')
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

                $this->storyComment->vote(Type::Down);
                $this->storyComment->refresh();

                $this->dispatch('story-comment-vote-updated', storyCommentId: $this->storyComment->id);
            })
            ->color('danger')
            ->icon($active ? 'heroicon-m-hand-thumb-down' : 'heroicon-o-hand-thumb-down')
            ->label($this->storyComment->formattedDownvoteCount())
            ->outlined(! $active)
            ->size(ActionSize::ExtraSmall)
            ->extraAttributes(['aria-pressed' => $active ? 'true' : 'false']);
    }

    #[On('story-comment-vote-updated')]
    public function refreshStoryComment(string $storyCommentId): void
    {
        if ($this->storyComment->id === $storyCommentId) {
            $this->storyComment->refresh();
        }
    }

    public function render(): View
    {
        return view('livewire.story-comment-vote.downvote-action');
    }
}
