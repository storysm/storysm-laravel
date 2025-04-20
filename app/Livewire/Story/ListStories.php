<?php

namespace App\Livewire\Story;

use App\Livewire\Story\Concerns\HasStoryTable;
use App\Models\Story;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListStories extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasStoryTable;

    public function table(Table $table): Table
    {
        return $this->getStoryTable($table);
    }

    public function render(): View
    {
        return view('livewire.story.list-stories');
    }
}
