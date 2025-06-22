<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
        <div class="flex flex-col gap-y-2">
            {{ trans_choice('story-comment.resource.model_label', 1) }}
        </div>
    </x-header>

    <x-container>
        <section class="flex flex-col max-w-2xl gap-4 mx-auto">
            <div>
                <x-filament::section>
                    <livewire:story-comment.story-comment-card :$storyComment :showActions="false" :showReplyButton="false" />
                </x-filament::section>
            </div>
            <div>
                <livewire:story-comment.story-comments-table :storyComment="$storyComment" />
            </div>
            <div>
                <livewire:story-comment.create-story-comment :storyComment="$storyComment" />
            </div>
        </section>
    </x-container>
    <x-filament-actions::modals />
</div>
