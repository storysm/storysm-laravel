<?php

namespace App\Filament\Resources\StoryCommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListStoryComments extends ListRecords
{
    protected static string $resource = StoryCommentResource::class;

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('parent_id'));
    }
}
