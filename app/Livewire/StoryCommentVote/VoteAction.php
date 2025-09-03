<?php

namespace App\Livewire\StoryCommentVote;

use App\Enums\Vote\Type;
use App\Models\StoryComment;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
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

abstract class VoteAction extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithRateLimiting;

    public StoryComment $storyComment;

    public function mount(StoryComment $storyComment): void
    {
        $this->storyComment = $storyComment;
    }

    abstract protected function getVoteType(): Type;

    abstract protected function getActionName(): string;

    abstract protected function getIcon(bool $active): string;

    abstract protected function getFormattedCount(): string;

    abstract protected function getActionColor(): ?string;

    /**
     * @return view-string
     */
    abstract protected function getViewName();

    public function voteAction(): Action
    {
        $active = $this->storyComment->currentUserVote()?->type === $this->getVoteType();

        return Action::make($this->getActionName())
            ->action(function () {
                try {
                    $this->rateLimit(30);
                } catch (TooManyRequestsException $exception) {
                    /** @var int $secondsUntilAvailable */
                    $secondsUntilAvailable = $exception->secondsUntilAvailable;

                    Notification::make()
                        ->title(__('story-comment-vote.notification.rate_limited.title'))
                        ->body(__('story-comment-vote.notification.rate_limited.body', ['seconds' => (string) $secondsUntilAvailable]))
                        ->danger()
                        ->send();

                    return;
                }

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

                $this->storyComment->vote($this->getVoteType());
                $this->storyComment->refresh();

                $this->dispatch('story-comment-vote-updated', storyCommentId: $this->storyComment->id);
            })
            ->color($this->getActionColor())
            ->icon($this->getIcon($active))
            ->label($this->getFormattedCount())
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
        return view($this->getViewName());
    }
}
