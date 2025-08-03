<?php

namespace App\Livewire\StoryComment;

use App\Models\Story;
use App\Models\StoryComment;
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

class StoryCommentsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
    }

    #[On('storyCommentCreated')]
    #[On('storyCommentDeleted')]
    public function refreshCommentsList(): void
    {
        $this->resetTable();
    }

    public function render(): View
    {
        return view('livewire.story-comment.story-comments-table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\View::make('components.story-comment.table.index')
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
                } elseif ($this->storyComment !== null) {
                    $count = $this->storyComment->reply_count;
                    $formattedCount = $this->storyComment->formattedReplyCount();
                }

                return new HtmlString(
                    '<div class="p-4 text-xl font-bold">'.
                    $formattedCount.' '.trans_choice('story-comment.resource.model_label', $count).
                    '</div>'
                );
            })
            ->paginated([10])
            ->query(StoryComment::query()
                ->when($this->story !== null, function (Builder $query) {
                    $query->where('story_id', $this->story?->id)
                        ->whereNull('parent_id');
                })
                ->when($this->storyComment !== null, function (Builder $query) {
                    $query->where('parent_id', $this->storyComment?->id);
                })
                ->with(['creator', 'userVote'])
            )
            ->queryStringIdentifier('comments');
    }
}
