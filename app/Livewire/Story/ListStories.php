<?php

namespace App\Livewire\Story;

use App\Livewire\Story\Concerns\HasStoryTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListStories extends Component implements HasForms, HasTable
{
    use HasStoryTable;
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            route('home') => __('navigation-menu.menu.home'),
            0 => trans_choice('story.resource.model_label', 2),
        ];
    }

    public function table(Table $table): Table
    {
        return $this->getStoryTable($table);
    }

    public function render(): View
    {
        return view('livewire.story.list-stories');
    }
}
