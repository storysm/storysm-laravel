<?php

namespace App\Filament\Resources\AgeRatingResource\Pages;

use App\Filament\Resources\AgeRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgeRating extends EditRecord
{
    protected static string $resource = AgeRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
