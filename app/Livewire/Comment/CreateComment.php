<?php

namespace App\Livewire\Comment;

use App\Concerns\HasLocales;
use App\Models\Comment;
use App\Models\Story;
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
class CreateComment extends Component implements HasForms
{
    use HasLocales;
    use InteractsWithForms;

    /**
     * @var array<string, string|string[]>|null
     */
    public ?array $data = [];

    public ?Story $story = null;

    public ?Comment $comment = null;

    /**
     * @param  ?Story  $story
     * @param  ?Comment  $comment
     */
    public function mount($story = null, $comment = null): void
    {
        // Ensure only one parent type is provided
        if ($story === null && $comment === null) {
            throw new \InvalidArgumentException('Either a Story or a Comment must be provided.');
        }
        if ($story !== null && $comment !== null) {
            throw new \InvalidArgumentException('Only one of Story or Comment can be provided.');
        }

        $this->story = $story;
        $this->comment = $comment;
        $this->form->fill();
    }

    public function createComment(): void
    {
        if (Auth::guest()) {
            Notification::make()
                ->title(__('comment.form.section.description.login_required'))
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        // Ensure at least one locale has content
        /** @var array<?string> */
        $body = $data['body'];
        $hasContent = collect($body)
            ->some(fn (?string $item) => $item !== null && trim($item) !== '');

        if (! $hasContent) {
            Notification::make()
                ->title(__('comment.form.validation.body_required'))
                ->danger()
                ->send();

            return;
        }

        $comment = new Comment;
        // Fill the translatable body
        $comment->fill($data);

        // Associate with the correct parent (Story or Comment)
        if ($this->story !== null) {
            $comment->story()->associate($this->story);
        } elseif ($this->comment !== null) {
            $comment->story()->associate($this->comment->story); // Associate with the parent comment's story
            $comment->parent()->associate($this->comment); // Set the parent comment
        }
        $comment->creator()->associate(Auth::user());
        $comment->save();

        Notification::make()
            ->title(__('comment.form.notification.created'))
            ->success()
            ->send();

        $this->form->fill(); // Reset the form

        // Dispatch event to notify other components
        $this->dispatch('commentCreated');
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
                                    ->label(__('comment.form.body.label'))
                                    ->lazy()
                                    ->placeholder(__('comment.form.body.placeholder'))
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
                            ->label(__('comment.form.actions.submit')),
                    ])
                    ->description($auth ? null : strval(__('comment.form.section.description.login_required')))
                    ->headerActions([
                        Action::make('login')
                            ->hidden($auth)
                            ->label(__('comment.form.actions.login'))
                            ->url(route('login')),
                    ])
                    ->heading($auth ? __('comment.form.section.heading.write') : __('comment.form.section.heading.login_required'))
                    ->icon($auth ? null : 'heroicon-o-exclamation-circle')
                    ->iconColor('warning')
                    ->key('comment'),
            ])
            ->disabled(! $auth)
            ->model(Comment::class)
            ->statePath('data');
    }

    public function render(): View
    {
        return view('livewire.comment.create-comment');
    }
}
