<?php

namespace App\Livewire\Story;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Actions extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Story $story;

    public ?string $class = null;

    public function mount(Story $story, ?string $class = null): void
    {
        $this->story = $story;
        $this->class = $class;
    }

    public function canEdit(): bool
    {
        return StoryResource::canEdit($this->story);
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->authorize($this->canEdit())
            ->icon('heroicon-m-pencil')
            ->url(route('filament.admin.resources.stories.edit', ['record' => $this->story]));
    }

    public function render(): View
    {
        return view('livewire.story.actions');
    }
}
