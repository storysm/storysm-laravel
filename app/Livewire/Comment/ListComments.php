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
use Livewire\Attributes\On;
use Livewire\Component;

class ListComments extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Story $story;

    public function mount(Story $story): void
    {
        $this->story = $story;
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
            ->query(Comment::query()
                ->where('story_id', $this->story->id)
                ->whereNull('parent_id')
                ->with('creator') // Eager load the creator relationship
            );
    }
}
