<?php

namespace App\Filament\Resources\StoryCommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class ListStoryComments extends ListRecords
{
    protected static string $resource = StoryCommentResource::class;

    public function getSubNavigation(): array
    {
        /** @var array<NavigationGroup|NavigationItem> */
        return static::getResource()::getRecordSubNavigation($this);
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query) {
                if ($parentId = Request::query('parent_id')) {
                    $query->where('parent_id', $parentId);
                } else {
                    $query->whereNull('parent_id');
                }
            });
    }
}
