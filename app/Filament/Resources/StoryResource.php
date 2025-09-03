<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Enums\Story\Status;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\UserResource\Utils\Creator;
use App\Models\Story;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class StoryResource extends Resource implements HasShieldPermissions
{
    use HasLocales;

    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 0;

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
                            ->locales(static::getSortedLocales())
                            ->suffixLocaleLabel(),
                        Translate::make()
                            ->schema([
                                TiptapEditor::make('content')
                                    ->label(__('story.resource.content')),
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

    public static function getNavigationGroup(): ?string
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
            ->columns(array_filter([
                CuratorColumn::make('cover_media_id')
                    ->label(__('story.resource.cover_media'))
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('story.resource.title'))
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (Story $record, $state) {
                        return match ($record->status) {
                            Status::Publish => $record->published_at > now() ? __('story.resource.status.pending') : $state,
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('upvote_count')
                    ->label(__('story-vote.resource.upvote_count'))
                    ->state(fn (Story $record) => $record->formattedUpvoteCount())
                    ->tooltip(fn (Story $record) => $record->upvote_count > 999 ? $record->upvote_count : null),
                Tables\Columns\TextColumn::make('downvote_count')
                    ->label(__('story-vote.resource.downvote_count'))
                    ->state(fn (Story $record) => $record->formattedDownvoteCount())
                    ->tooltip(fn (Story $record) => $record->downvote_count > 999 ? $record->downvote_count : null),
                Tables\Columns\TextColumn::make('comment_count')
                    ->label(__('story-comment.resource.comment_count'))
                    ->state(fn (Story $record) => $record->formattedCommentCount())
                    ->tooltip(fn (Story $record) => $record->comment_count > 999 ? $record->comment_count : null),
                static::canViewAll() ? Tables\Columns\TextColumn::make('creator.name')
                    ->label(ucfirst(__('validation.attributes.creator'))) : null,
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->label(__('story.resource.published_at'))
                    ->toggleable()
                    ->sortable(),
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
                    Tables\Actions\ViewAction::make()
                        ->url(fn (Story $record) => route('stories.show', $record)),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Story $record) => route('filament.admin.resources.stories.edit', $record));
    }
}
