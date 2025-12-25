<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Resources\Pages\CreateRecord;

class CreateStory extends CreateRecord
{
    protected static string $resource = StoryResource::class;

    public function afterCreate(): void
    {
        /** @var Story $story */
        $story = $this->getRecord();
        StoryResource::afterSave($story);
    }
}
