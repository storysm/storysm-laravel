<?php

namespace App\Livewire\Story\Concerns;

use App\Models\Story;
use Filament\Tables;
use Filament\Tables\Table;

trait HasStoryTable
{
    /**
     * Defines the base table structure for Stories.
     */
    protected function getStoryTable(Table $table): Table
    {
        $query = Story::published();

        return $table
            ->query($query)
            ->contentGrid(function () {
                return [
                    'default' => 1,
                    'md' => 2,
                    'lg' => 3,
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
}
