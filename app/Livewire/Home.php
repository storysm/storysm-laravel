<?php

namespace App\Livewire;

use App\Livewire\Story\Concerns\HasStoryTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Home extends Component implements HasForms, HasTable
{
    const HOME_PAGE_LIMIT = 12;

    use HasStoryTable;
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * The component's listeners.
     *
     * @var array<string, string>
     */
    protected $listeners = [
        'resetAge' => '$refresh',
    ];

    public function table(Table $table): Table
    {
        $table = $this->getStoryTable($table);
        $this->disableSort($table);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->limit(self::HOME_PAGE_LIMIT))
            ->searchable(false)
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('view_all_stories')
                    ->icon('heroicon-o-arrow-right')
                    ->iconPosition(IconPosition::After)
                    ->label(__('story.table.view_all'))
                    ->url(route('stories.index')),
            ]);
    }

    public function render(): View
    {
        return view('livewire.home');
    }

    /**
     * Disables sorting on all columns in the given table.
     */
    private function disableSort(Table $table): void
    {
        $columns = $table->getColumns();
        collect($columns)->each(function (Tables\Columns\Column $column) {
            if ($column->isSortable()) {
                $column->sortable(false);
            }
        });
    }
}
