<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Rules\UniqueJsonTranslation;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class CategoryResource extends Resource
{
    use HasLocales;

    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Translate::make()
                ->schema(function (Get $get, string $locale): array {
                    /** @var array<?string> */
                    $names = $get('name');
                    $required = collect($names)->every(fn ($item) => $item === null || trim($item) === '');

                    return [
                        TextInput::make('name')
                            ->label(__('category.resource.name'))
                            ->required($required)
                            ->maxLength(255)
                            ->rules(function (Get $get) use ($locale) {
                                /** @var string */
                                $id = $get('id');

                                return [
                                    new UniqueJsonTranslation(table: 'categories', column: 'name', locale: $locale, ignoreId: $id),
                                ];
                            }),
                        TiptapEditor::make('description')
                            ->label(__('category.resource.description')),
                    ];
                })
                ->columnSpanFull()
                ->locales(static::getSortedLocales())
                ->suffixLocaleLabel(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
