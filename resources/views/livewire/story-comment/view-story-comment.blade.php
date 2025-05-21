<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
        <div class="flex flex-col gap-y-2">
            {{ trans_choice('story-comment.resource.model_label', 1) }}
        </div>
    </x-header>

    <x-container>
        <section class="flex flex-col gap-y-8">
            <div class="grid items-start w-full grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4 lg:sticky lg:top-4">
                    <div>
                        <x-filament::section>
                            <livewire:story-comment.story-comment-card :$storyComment :showReplies="false" />
                        </x-filament::section>
                    </div>
                </div>
                <div class="flex flex-col gap-4 lg:col-span-8">
                    <livewire:story-comment.list-story-comments :storyComment="$storyComment" />
                    <livewire:story-comment.create-story-comment :storyComment="$storyComment" />
                </div>
            </div>
        </section>
    </x-container>
</div>
