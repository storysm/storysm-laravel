<div>
    <div
        class="flex flex-row items-center justify-center ring-1 ring-gray-950/5 dark:ring-white/10 rounded-bl-xl size-8 aspect-square {{ $this->class }}">
        <x-filament-actions::group :actions="[$this->viewAction, $this->editAction]" />
    </div>
</div>
