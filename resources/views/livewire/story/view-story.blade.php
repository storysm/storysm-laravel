<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
        <div class="flex flex-col gap-y-2">
            {{ $story->title }}

            <x-story.meta icon="heroicon-m-eye" iconClass="size-3">
                <p class="text-sm">{{ $story->formattedViewCount() }}</p>
            </x-story.meta>
        </div>
    </x-header>

    <x-container>
        <section class="flex flex-col gap-y-8">
            <div class="grid items-start w-full grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="flex flex-col gap-4 lg:col-span-8">
                    <x-filament::section>
                        <div class="prose dark:prose-invert max-w-fit">
                            {!! $story->content !!}
                        </div>
                    </x-filament::section>
                    <x-filament::section>
                        <livewire:vote.upvote-action :story="$story" />
                    </x-filament::section>
                </div>
                <div class="sm:col-span-4">
                    <div>
                        <x-filament::section></x-filament::section>
                    </div>
                </div>
            </div>
        </section>
    </x-container>
</div>
