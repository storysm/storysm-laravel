<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\AgeRatingResource\Pages;
use App\Models\AgeRating;
use App\Rules\UniqueJsonTranslation;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class AgeRatingResource extends Resource implements HasShieldPermissions
{
    use HasLocales;

    protected static ?string $model = AgeRating::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Translate::make()
                    ->schema(function (string $locale): array {
                        return [
                            TextInput::make('name')
                                ->label(__('age_rating.resource.name'))
                                ->required($locale === config('app.locale'))
                                ->maxLength(255)
                                ->rules(function (Get $get) use ($locale) {
                                    /** @var string */
                                    $id = $get('id');

                                    return [
                                        new UniqueJsonTranslation(table: 'age_ratings', column: 'name', locale: $locale, ignoreId: $id),
                                    ];
                                }),
                            TiptapEditor::make('description')
                                ->label(__('age_rating.resource.description')),
                        ];
                    })
                    ->columnSpanFull()
                    ->locales(static::getSortedLocales())
                    ->suffixLocaleLabel(),
                Section::make([
                    TextInput::make('age_representation')
                        ->label(__('age_rating.resource.age_representation'))
                        ->required()
                        ->integer()
                        ->minValue(0)
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('age_rating.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgeRatings::route('/'),
            'create' => Pages\CreateAgeRating::route('/create'),
            'edit' => Pages\EditAgeRating::route('/{record}/edit'),
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
        return trans_choice('age_rating.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('age_rating.resource.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('age_representation')
                    ->label(__('age_rating.resource.age_representation'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('stories_count')
                    ->counts('stories')
                    ->label(__('age_rating.resource.stories_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('age_rating.resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('age_rating.resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
