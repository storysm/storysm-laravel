<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Enums\Page\Status;
use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\UserResource\Utils\Creator;
use App\Models\Page;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PageResource extends Resource implements HasShieldPermissions
{
    use HasLocales;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

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
                    'sm' => 6,
                ])->schema([
                    Forms\Components\Group::make([
                        Translate::make()
                            ->schema(function (Get $get) {
                                /** @var array<?string> */
                                $titles = $get('title');
                                $required = collect($titles)->every(fn ($item) => $item === null || trim($item) === '');

                                return [
                                    Forms\Components\Textarea::make('title')
                                        ->label(__('page.resource.title'))
                                        ->lazy()
                                        ->required($required),
                                ];
                            })
                            ->columnSpanFull()
                            ->locales(static::getSortedLocales())
                            ->suffixLocaleLabel(),
                        Translate::make()
                            ->schema([
                                TiptapEditor::make('content')
                                    ->label(__('page.resource.content')),
                            ])
                            ->columnSpanFull()
                            ->locales(static::getSortedLocales())
                            ->suffixLocaleLabel(),
                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 4,
                    ]),
                    Forms\Components\Group::make([
                        Forms\Components\Section::make([
                            Forms\Components\Radio::make('status')
                                ->default(Status::Draft)
                                ->options(Status::class)
                                ->required(),
                        ]),
                        Creator::getComponent(static::canViewAll()),
                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                ]),
            ]);
    }

    /**
     * @return Builder<Page>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Page> */
        $query = parent::getEloquentQuery();

        if (! static::canViewAll()) {
            $query->whereCreatorId(User::auth()?->id);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return trans_choice('page.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_all',
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('page.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(array_filter([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('page.resource.title')),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                static::canViewAll() ? Tables\Columns\TextColumn::make('creator.name')
                    ->label(ucfirst(__('validation.attributes.creator')))
                    ->searchable() : null,
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label(ucfirst(__('validation.attributes.created_at')))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label(ucfirst(__('validation.attributes.updated_at')))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
