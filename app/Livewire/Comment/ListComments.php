<?php

namespace App\Livewire\Comment;

use App\Models\Comment;
use App\Models\Story;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;

class ListComments extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
    }

    #[On('commentCreated')]
    public function refreshCommentsList(): void
    {
        $this->resetTable();
    }

    public function render(): View
    {
        return view('livewire.comment.list-comments');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\View::make('components.comment.table.index')
                    ->schema([
                        Tables\Columns\TextColumn::make('created_at')
                            ->label(ucfirst(__('validation.attributes.created_at')))
                            ->sortable(),
                    ]),
            ])
            ->contentGrid(function () {
                return [
                    'default' => 1,
                ];
            })
            ->defaultSort('created_at', 'desc')
            ->header(function () {
                $count = 0;
                $formattedCount = '';
                if ($this->story !== null) {
                    $count = $this->story->comment_count;
                    $formattedCount = $this->story->formattedCommentCount();
                } elseif ($this->comment !== null) {
                    $count = $this->comment->reply_count;
                    $formattedCount = $this->comment->formattedReplyCount();
                }

                return new HtmlString(
                    '<div class="p-4 text-xl font-bold">'.
                    $formattedCount.' '.trans_choice('comment.resource.model_label', $count).
                    '</div>'
                );
            })
            ->paginated([10])
            ->query(Comment::query()
                ->when($this->story !== null, function (Builder $query) {
                    $query->where('story_id', $this->story?->id)
                        ->whereNull('parent_id');
                })
                ->when($this->comment !== null, function (Builder $query) {
                    $query->where('parent_id', $this->comment?->id);
                })
                ->with('creator')
            )
            ->queryStringIdentifier('comments');
    }
}
