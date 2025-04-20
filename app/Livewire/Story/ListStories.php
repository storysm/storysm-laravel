<?php

namespace App\Livewire\Story;

use App\Models\Story;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListStories extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = Story::published();

        return $table
            ->query($query)
            ->contentGrid(function () {
                return [
                    'default' => 1,
                    'md' => 2,
                    'lg' => 3,
                    'xl' => 4,
                ];
            })
            ->columns([
                Tables\Columns\Layout\View::make('components.story.table.index')
                    ->schema([
                        Tables\Columns\TextColumn::make('title')
                            ->label(__('story.resource.title'))
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('creator.name')
                            ->label(ucfirst(__('validation.attributes.creator')))
                            ->sortable(),
                        Tables\Columns\TextColumn::make('published_at')
                            ->dateTime()
                            ->label(__('story.resource.published_at'))
                            ->sortable(),
                        Tables\Columns\TextColumn::make('updated_at')
                            ->dateTime()
                            ->label(ucfirst(__('validation.attributes.updated_at')))
                            ->sortable(),
                    ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.story.list-stories');
    }
}
