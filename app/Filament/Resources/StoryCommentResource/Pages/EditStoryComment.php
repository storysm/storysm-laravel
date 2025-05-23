<?php

namespace App\Filament\Resources\StoryCommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoryComment extends EditRecord
{
    protected static string $resource = StoryCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
