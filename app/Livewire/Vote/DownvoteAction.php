<?php

namespace App\Livewire\Vote;

use App\Enums\Vote\Type;
use App\Models\Story;
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

class DownvoteAction extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Story $story;

    public function mount(Story $story): void
    {
        $this->story = $story;
    }

    public function downvoteAction(): Action
    {
        $active = $this->story->currentUserVote()?->type === Type::Down;

        return Action::make('downvote')
            ->action(function () {
                if (! Auth::check()) {
                    Notification::make()
                        ->title(__('vote.notification.login_required.title'))
                        ->body(__('vote.notification.login_required.body'))
                        ->warning()
                        ->actions([
                            Notifications\Actions\Action::make('login')
                                ->label(__('vote.notification.login_required.action.login'))
                                ->url(route('login'))
                                ->button(),
                        ])
                        ->send();

                    return;
                }

                $this->story->vote(Type::Down);
                $this->story->refresh();

                $this->dispatch('vote-updated', storyId: $this->story->id);
            })
            ->color('danger')
            ->icon($active ? 'heroicon-m-hand-thumb-down' : 'heroicon-o-hand-thumb-down')
            ->label($this->story->formattedDownvoteCount())
            ->outlined(! $active);
    }

    #[On('vote-updated')]
    public function refreshStory(string $storyId): void
    {
        if ($this->story->id === $storyId) {
            $this->story->refresh();
        }
    }

    public function render(): View
    {
        return view('livewire.vote.downvote-action');
    }
}
