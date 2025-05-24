<?php

namespace App\Filament\Resources\StoryCommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use App\Models\StoryComment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoryComment extends EditRecord
{
    protected static string $resource = StoryCommentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\ViewAction::make()
                ->url(route('story-comments.show', $this->record)),
        ];

        if ($this->record instanceof StoryComment) {
            if ($this->record->parent_id) {
                $actions[] = Actions\ViewAction::make('view_parent')
                    ->label(__('View :name', ['name' => __('story-comment.resource.parent_comment')]))
                    ->url(fn (): string => route('story-comments.show', $this->record->parent_id))
                    ->visible(fn (): bool => StoryComment::where('id', $this->record->parent_id)->exists());
            }

            $actions[] = Actions\ViewAction::make('view_story')
                ->label(__('View :name', ['name' => trans_choice('story.resource.model_label', 1)]))
                ->url(fn (): string => route('stories.show', $this->record->story_id));
        }

        $actions[] = Actions\DeleteAction::make();

        return $actions;
    }
}
