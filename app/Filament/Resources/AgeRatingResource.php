<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgeRatingResource\Pages;
use App\Models\AgeRating;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgeRatingResource extends Resource
{
    protected static ?string $model = AgeRating::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgeRatings::route('/'),
            'create' => Pages\CreateAgeRating::route('/create'),
            'edit' => Pages\EditAgeRating::route('/{record}/edit'),
        ];
    }
}
