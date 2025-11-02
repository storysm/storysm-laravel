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
                            <livewire:story-vote.upvote-action :story="$story" />
                            <livewire:story-vote.downvote-action :story="$story" />
                        </div>
                    </x-filament::section>
                    @if ($story->creator->can(\App\Constants\Permissions::ACT_AS_GUEST_USER))
                        <x-filament::section class="">
                            <div class="flex flex-row gap-x-2">
                                <x-filament::icon icon="heroicon-o-information-circle"
                                    class="w-5 h-5 mt-1 text-warning-500 dark:text-warning-400" />
                                <p>{{ __('user.resource.guest_user_notice') }}</p>
                            </div>
                        </x-filament::section>
                    @endif
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
