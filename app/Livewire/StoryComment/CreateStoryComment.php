<?php

namespace App\Livewire\StoryComment;

use App\Concerns\HasLocales;
use App\Models\Story;
use App\Models\StoryComment;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

/**
 * @property Form $form
 */
class CreateStoryComment extends Component implements HasForms
{
    use HasLocales;
    use InteractsWithForms;

    /**
     * @var array<string, string|string[]>|null
     */
    public ?array $data = [];

    public ?Story $story = null;

    public ?StoryComment $storyComment = null;

    /**
     * @param  ?Story  $story
     * @param  ?StoryComment  $storyComment
     */
    public function mount($story = null, $storyComment = null): void
    {
        // Ensure only one parent type is provided
        if ($story === null && $storyComment === null) {
            throw new \InvalidArgumentException('Either a Story or a StoryComment must be provided.');
        }
        if ($story !== null && $storyComment !== null) {
            throw new \InvalidArgumentException('Only one of Story or StoryComment can be provided.');
        }

        $this->story = $story;
        $this->storyComment = $storyComment;
        $this->form->fill();
    }

    public function createComment(): void
    {
        $this->authorize('create', StoryComment::class);

        $data = $this->form->getState();

        // Ensure at least one locale has content
        /** @var array<?string> */
        $body = $data['body'];
        $hasContent = collect($body)
            ->some(fn (?string $item) => $item !== null && trim($item) !== '');

        if (! $hasContent) {
            Notification::make()
                ->title(__('story-comment.form.validation.body_required'))
                ->danger()
                ->send();

            return;
        }

        $storyComment = new StoryComment;
        // Fill the translatable body
        $storyComment->fill($data);

        // Associate with the correct parent (Story or StoryComment)
        if ($this->story !== null) {
            $storyComment->story()->associate($this->story);
        } elseif ($this->storyComment !== null) {
            $storyComment->story()->associate($this->storyComment->story); // Associate with the parent StoryComment's story
            $storyComment->parent()->associate($this->storyComment); // Set the parent StoryComment
        }
        $storyComment->creator()->associate(Auth::user());
        $storyComment->save();

        Notification::make()
            ->title(__('story-comment.form.notification.created'))
            ->success()
            ->send();

        $this->form->fill(); // Reset the form

        // Dispatch event to notify other components
        $this->dispatch('storyCommentCreated');
    }

    public function form(Form $form): Form
    {
        $auth = Auth::check();

        return $form
            ->schema([
                Section::make([
                    Translate::make()
                        ->schema(function (Get $get) {
                            /** @var array<?string> */
                            $titles = $get('body');
                            $required = collect($titles)->every(fn ($item) => $item === null || trim($item) === '');

                            return [
                                Textarea::make('body')
                                    ->label(__('story-comment.form.body.label'))
                                    ->lazy()
                                    ->placeholder(__('story-comment.form.body.placeholder'))
                                    ->required($required),
                            ];
                        })
                        ->columnSpanFull()
                        ->locales(static::getSortedLocales())
                        ->suffixLocaleLabel(),
                ])
                    ->footerActions([
                        Action::make('submit')
                            ->action('createComment')
                            ->disabled(! $auth)
                            ->label(__('story-comment.form.actions.submit')),
                    ])
                    ->description($auth ? null : strval(__('story-comment.form.section.description.login_required')))
                    ->headerActions([
                        Action::make('login')
                            ->hidden($auth)
                            ->label(__('story-comment.form.actions.login'))
                            ->url(route('login')),
                    ])
                    ->heading($auth ? __('story-comment.form.section.heading.write') : __('story-comment.form.section.heading.login_required'))
                    ->icon($auth ? null : 'heroicon-o-exclamation-circle')
                    ->iconColor('warning')
                    ->key('comment'),
            ])
            ->disabled(! $auth)
            ->model(StoryComment::class)
            ->statePath('data');
    }

    public function render(): View
    {
        return view('livewire.story-comment.create-story-comment');
    }
}
