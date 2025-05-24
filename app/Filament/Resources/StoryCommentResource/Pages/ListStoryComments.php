<?php

namespace App\Filament\Resources\StoryCommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use App\Models\StoryComment;
use Filament\Actions;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class ListStoryComments extends ListRecords
{
    protected static string $resource = StoryCommentResource::class;

    protected function getHeaderActions(): array
    {
        $parentId = Request::query('parent_id');
        if (! $parentId) {
            return [];
        }

        $parentComment = StoryComment::find($parentId);
        if (! $parentComment) {
            return [];
        }

        return [
            Actions\ViewAction::make('view_parent')
                ->label(__('View :name', ['name' => __('story-comment.resource.parent_comment')]))
                ->url(fn (): string => route('story-comments.show', $parentComment)),

            Actions\ViewAction::make('view_story')
                ->label(__('View :name', ['name' => trans_choice('story.resource.model_label', 1)]))
                ->url(fn (): string => route('stories.show', $parentComment->story)),
        ];
    }

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
