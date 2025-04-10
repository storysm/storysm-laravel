<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\UserResource\Utils\Creator;
use App\Models\Story;
use App\Utils\Locale;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class StoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAll(): bool
    {
        return static::can('viewAll');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make([
                    'default' => 1,
                    'md' => 6,
                ])->schema([
                    Forms\Components\Group::make([
                        Translate::make()
                            ->schema(function (Get $get) {
                                /** @var array<?string> */
                                $titles = $get('title');
                                $required = collect($titles)->every(fn ($item) => $item === null || trim($item) === '');

                                return [
                                    Forms\Components\Textarea::make('title')
                                        ->label(__('story.resource.title'))
                                        ->lazy()
                                        ->required($required),
                                ];
                            })
                            ->columnSpanFull()
                            ->locales(Locale::getSortedLocales())
                            ->suffixLocaleLabel(),
                        Translate::make()
                            ->schema([
                                TiptapEditor::make('content')
                                    ->label(__('story.resource.content')),
                            ])
                            ->columnSpanFull()
                            ->locales(Locale::getSortedLocales())
                            ->suffixLocaleLabel(),
                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 4,
                    ]),
                    Forms\Components\Group::make([
                        Forms\Components\Section::make([
                            Forms\Components\DateTimePicker::make('published_at')
                                ->default(now()),
                        ]),
                        Forms\Components\Section::make([
                            CuratorPicker::make('cover_media_id')
                                ->buttonLabel(__('story.resource.select_cover_media'))
                                ->extraAttributes(['class' => 'sm:w-fit'])
                                ->label(__('story.resource.cover_media'))
                                ->relationship('coverMedia', 'name'),
                        ]),
                        Creator::getComponent(static::canViewAll()),
                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                ]),
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('story.resource.model_label', 1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_all',
        ];
    }

    /**
     * @return Builder<Story>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::canViewAll()) {
            $query->where('creator_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('story.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cover_media_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ]);
    }
}
