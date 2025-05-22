<?php

namespace App\Filament\Resources;

use App\Concerns\HasLocales;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\UserResource\Utils\Creator;
use App\Models\StoryComment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class StoryCommentResource extends Resource
{
    use HasLocales;

    protected static ?string $model = StoryComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    protected static ?int $navigationSort = 1;

    public static function canViewAll(): bool
    {
        return static::can('viewAll');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Livewire::make('story-comment.story-comment-card',
                        fn (Get $get): array => [
                            'storyComment' => StoryComment::find($get('parent_id')),
                            'showReplies' => false,
                        ]),
                ]),
                Translate::make()
                    ->schema(function (Get $get) {
                        /** @var array<?string> */
                        $titles = $get('body');
                        $required = collect($titles)->every(fn ($item) => $item === null || trim($item) === '');

                        return [
                            Forms\Components\Textarea::make('body')
                                ->label(__('story-comment.form.body.label'))
                                ->lazy()
                                ->placeholder(__('story-comment.form.body.placeholder'))
                                ->required($required),
                        ];
                    })
                    ->columnSpanFull()
                    ->locales(static::getSortedLocales())
                    ->suffixLocaleLabel(),
                Creator::getComponent(static::canViewAll()),
            ]);
    }

    /**
     * @return Builder<StoryComment>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::canViewAll()) {
            $query->where('creator_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans_choice('story.resource.model_label', 1);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('story-comment.resource.model_label', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('story-comment.resource.model_label', 2);
    }

    /**
     * @return array<string>
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_all',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoryComments::route('/'),
            'edit' => Pages\EditStoryComment::route('/{record}/edit'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(array_filter([
                Tables\Columns\TextColumn::make('body')
                    ->label(trans_choice('story-comment.resource.model_label', 1))
                    ->limit(30),
                Tables\Columns\TextColumn::make('story.title')
                    ->label(trans_choice('story.resource.model_label', 1))
                    ->limit(30),
                Tables\Columns\TextColumn::make('parent.body')
                    ->label(__('story-comment.resource.replied_comment'))
                    ->limit(30),
                Tables\Columns\TextColumn::make('reply_count')
                    ->label(__('story-comment.resource.reply_count')),
                static::canViewAll() ? Tables\Columns\TextColumn::make('creator.name')
                    ->label(ucfirst(__('validation.attributes.creator'))) : null,
            ]))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (StoryComment $record) => route('story-comments.show', $record)),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn (StoryComment $record) => ! $record->parent)
                        ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                        ->label(__('View :name', ['name' => __('story-comment.resource.replied_comment')]))
                        ->url(fn (StoryComment $record) => $record->parent ? route('story-comments.show', $record->parent) : null),
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-document-text')
                        ->label(__('View :name', ['name' => trans_choice('story.resource.model_label', 1)]))
                        ->url(fn (StoryComment $record) => route('stories.show', $record->story)),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (StoryComment $record) => route('story-comments.show', $record));
    }
}
