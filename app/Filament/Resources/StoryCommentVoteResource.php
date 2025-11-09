<?php

namespace App\Filament\Resources;

use App\Enums\Vote\Type;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\StoryCommentVoteResource\Pages;
use App\Models\StoryCommentVote;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoryCommentVoteResource extends Resource
{
    protected static ?string $model = StoryCommentVote::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-thumb-up';

    protected static ?int $navigationSort = 3;

    public static function canViewAll(): bool
    {
        return static::can('viewAll');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    /**
     * @return Builder<StoryCommentVote>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::canViewAll()) {
            $query->where('creator_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoryCommentVotes::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans_choice('story-comment-vote.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return trans_choice('story.resource.model_label', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('story-comment-vote.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comment.body')
                    ->label(trans_choice('story-comment.resource.model_label', 1))
                    ->limit(50),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(trans_choice('user.resource.model_label', 1)),
                Tables\Columns\IconColumn::make('type')
                    ->label(__('story-comment-vote.type')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(Type::class)
                    ->label(__('story-comment-vote.type')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (StoryCommentVote $record) => route('story-comments.show', $record->comment))
                        ->visible(fn (StoryCommentVote $record): bool => (bool) $record->comment),
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
