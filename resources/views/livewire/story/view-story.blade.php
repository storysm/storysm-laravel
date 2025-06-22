<div>
    <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
        <div class="flex flex-col gap-y-2">
            {{ $story->title }}

            <div class="flex flex-row gap-2">
                <x-story.meta icon="heroicon-m-eye" iconClass="size-3">
                    <p class="text-sm">{{ $story->formattedViewCount() }}</p>
                </x-story.meta>

                <x-story.meta icon="heroicon-m-chat-bubble-oval-left-ellipsis" iconClass="size-3">
                    <p class="text-sm">{{ $story->formattedCommentCount() }}</p>
                </x-story.meta>
            </div>
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
                        <div class="flex flex-row space-x-2">
                            <livewire:vote.upvote-action :story="$story" />
                            <livewire:vote.downvote-action :story="$story" />
                        </div>
                    </x-filament::section>
                    <livewire:story-comment.create-story-comment :story="$story" />
                    <livewire:story-comment.story-comments-table :story="$story" />
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
