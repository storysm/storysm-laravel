<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\CommentResource\Pages;
use App\Models\StoryComment;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoryCommentResource extends Resource
{
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
                //
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
                Tables\Columns\TextColumn::make('reply_count')
                    ->label(__('story-comment.resource.reply_count')),
                static::canViewAll() ? Tables\Columns\TextColumn::make('creator.name')
                    ->label(ucfirst(__('validation.attributes.creator'))) : null,
            ]))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
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
            ]);
    }
}
