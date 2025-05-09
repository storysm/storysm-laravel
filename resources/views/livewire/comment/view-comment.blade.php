<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
        <div class="flex flex-col gap-y-2">
            {{ trans_choice('comment.resource.model_label', 1) }}
        </div>
    </x-header>

    <x-container>
        <section class="flex flex-col gap-y-8">
            <div class="grid items-start w-full grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4 lg:sticky lg:top-4">
                    <div>
                        <x-filament::section>
                            <x-comment.card :comment="$comment" :showReplies="false" />
                        </x-filament::section>
                    </div>
                </div>
                <div class="flex flex-col gap-4 lg:col-span-8">
                    <livewire:comment.list-comments :comment="$comment" />
                    <livewire:comment.create-comment :comment="$comment" />
                </div>
            </div>
        </section>
    </x-container>
</div>
