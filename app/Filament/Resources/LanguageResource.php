<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\LanguageResource\Pages;
use App\Models\Language;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LanguageResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Language::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('code')
                        ->label(__('language.code'))
                        ->required()
                        ->maxLength(8)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')
                        ->label(__('language.name'))
                        ->required()
                        ->maxLength(255),
                ]),
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('language.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('language.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('language.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('language.name'))
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ]);
    }
}
