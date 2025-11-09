<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\LicenseResource\Pages;
use App\Models\License;
use App\Rules\UniqueJsonTranslation;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class LicenseResource extends Resource implements HasShieldPermissions
{
    use HasLocales;

    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Translate::make()
                    ->schema(function (Get $get, string $locale): array {
                        /** @var array<?string> */
                        $names = $get('name');
                        $required = collect($names)->every(fn ($item) => $item === null || trim($item) === '');

                        return [
                            TextInput::make('name')
                                ->label(__('license.resource.name'))
                                ->required($required)
                                ->maxLength(255)
                                ->rules(function (Get $get) use ($locale) {
                                    /** @var string */
                                    $id = $get('id');

                                    return [
                                        new UniqueJsonTranslation(table: 'licenses', column: 'name', locale: $locale, ignoreId: $id),
                                    ];
                                }),
                            TiptapEditor::make('description')
                                ->label(__('license.resource.description')),
                        ];
                    })
                    ->columnSpanFull()
                    ->locales(static::getSortedLocales())
                    ->suffixLocaleLabel(),
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('license.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
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
        return trans_choice('license.resource.model_label', 2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('license.resource.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stories_count')
                    ->counts('stories')
                    ->label(__('license.resource.stories_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('license.resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('license.resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ]);
    }
}
