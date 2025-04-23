<?php

namespace App\Filament\Resources;

use App\Enums\Vote\Type;
use App\Filament\Actions\Tables\ReferenceAwareDeleteBulkAction;
use App\Filament\Resources\VoteResource\Pages;
use App\Models\Vote;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VoteResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Vote::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-thumb-up';

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
     * @return Builder<Vote>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::canViewAll()) {
            $query->where('creator_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return trans_choice('vote.resource.model_label', 1);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVotes::route('/'),
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

    public static function getPluralModelLabel(): string
    {
        return trans_choice('vote.resource.model_label', 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (Vote $record) => route('stories.show', $record->story)),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ReferenceAwareDeleteBulkAction::make(),
                ]),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('story.title')
                    ->label(trans_choice('story.resource.model_label', 1)),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(trans_choice('user.resource.model_label', 1)),
                Tables\Columns\IconColumn::make('type')
                    ->label(trans_choice('vote.resource.model_label', 1)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(trans_choice('vote.resource.model_label', 1))
                    ->options(Type::class),
            ]);
    }
}
