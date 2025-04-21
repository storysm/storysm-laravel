<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()">
        {{ trans_choice('story.resource.model_label', 2) }}
    </x-header>
    <x-container>
        {{ $this->table }}
    </x-container>
</div>
