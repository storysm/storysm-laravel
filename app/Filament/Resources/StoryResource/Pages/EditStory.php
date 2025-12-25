<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStory extends EditRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->url(fn () => route('stories.show', $this->getRecord())),
            Actions\DeleteAction::make(),
        ];
    }

    public function afterSave(): void
    {
        /** @var Story $story */
        $story = $this->getRecord();
        StoryResource::afterSave($story);
    }
}
