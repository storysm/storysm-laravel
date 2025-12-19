<div x-data @scroll.window.throttle.50ms="if(!$store.reader.fullscreen) $dispatch('reader-scroll', { event: $event })"
    class="relative">
    <div x-show="!$store.reader.fullscreen">
        <x-header :breadcrumbs="$this->getBreadcrumbs()" :actions="$this->getActions()">
            <div class="flex flex-col gap-y-2">
                {{ $story->title }}

                <x-story.meta icon="heroicon-m-user" iconClass="size-4" title="{{ $story->creator->name }}">
                    <p class="text-base">{{ $story->creator->name }}</p>
                </x-story.meta>

                <div class="flex flex-row gap-2">
                    @if ($story->view_count > 500)
                        <x-story.meta icon="heroicon-m-eye" iconClass="size-3">
                            <p class="text-sm">{{ $story->formattedViewCount() }}</p>
                        </x-story.meta>
                    @endif

                    <x-story.meta icon="heroicon-m-chat-bubble-oval-left-ellipsis" iconClass="size-3">
                        <p class="text-sm">{{ $story->formattedCommentCount() }}</p>
                    </x-story.meta>

                    <x-story.meta icon="heroicon-m-hand-thumb-up" iconClass="size-3">
                        <p class="text-sm">{{ $story->formattedUpvoteCount() }}</p>
                    </x-story.meta>

                    <x-story.meta icon="heroicon-m-calendar"
                        title="{{ $story->published_at?->format('M d, Y') ?? 'N/A' }}">
                        <p class="text-sm">{{ $story->published_at?->diffForHumans() ?? 'N/A' }}</p>
                    </x-story.meta>

                    <x-story.meta icon="heroicon-m-document-text">
                        <p class="text-sm">{{ $story->licenses->first()?->name ?? 'N/A' }}</p>
                    </x-story.meta>

                    <x-story.meta icon="heroicon-m-shield-check">
                        <p class="text-sm">
                            @if ($story->age_rating_effective_value)
                                {{ $story->age_rating_effective_value }}+
                            @else
                                N/A
                            @endif
                        </p>
                    </x-story.meta>
                </div>
            </div>
        </x-header>
    </div>

    <x-container>
        <section>
            <div class="grid items-start w-full grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="flex flex-col gap-4 transition-colors duration-300"
                    x-bind:class="{
                        'fixed inset-0 z-30 overflow-y-auto p-4 md:p-12 lg:col-span-12': $store.reader.fullscreen,
                        'lg:col-start-3 lg:col-span-8': !$store.reader.fullscreen,
                        'bg-white': $store.reader.theme === 'light' && $store.reader.fullscreen,
                        'bg-sepia-100': $store.reader.theme === 'sepia' && $store.reader.fullscreen,
                        'bg-gray-900': $store.reader.theme === 'dark' && $store.reader.fullscreen
                    }"
                    @scroll.throttle.50ms="if($store.reader.fullscreen) $dispatch('reader-scroll', { event: $event })">
                    <x-filament::section
                        x-bind:class="$store.reader.fullscreen ? 'shadow-none !bg-transparent border-0' : ''">

                        <div class="mx-auto transition-all duration-300 max-w-none"
                            x-bind:class="{
                                'prose dark:prose-invert': !$store.reader.fullscreen,
                                'prose': $store.reader.theme === 'light' && $store.reader.fullscreen,
                                'prose prose-sepia': $store.reader.theme === 'sepia' && $store.reader.fullscreen,
                                'prose prose-invert': $store.reader.theme === 'dark' && $store.reader.fullscreen,
                                'font-sans': $store.reader.font === 'sans' && $store.reader.fullscreen,
                                'font-serif': $store.reader.font === 'serif' && $store.reader.fullscreen,
                                'font-mono': $store.reader.font === 'mono' && $store.reader.fullscreen
                            }"
                            x-bind:style="$store.reader.fullscreen ?
                                `font-size: ${$store.reader.fontSize}%; max-width: ${$store.reader.maxWidth}ch;` : ''">
                            <h1 x-show="$store.reader.fullscreen" class="mb-8 text-3xl font-bold text-center">
                                {{ $story->title }}
                            </h1>

                            {!! $story->content !!}
                        </div>

                        <div x-show="$store.reader.fullscreen" class="h-24 md:hidden"></div>
                    </x-filament::section>

                    <div class="flex flex-col gap-4"
                        x-bind:class="$store.reader.fullscreen ? 'max-w-2xl mx-auto w-full pb-12' : ''"
                        x-show="!$store.reader.fullscreen">
                        <x-filament::section x-bind:class="$store.reader.fullscreen ? '!bg-transparent border-0' : ''">
                            <div class="flex flex-row space-x-2 justify-center">
                                <livewire:story-vote.upvote-action :story="$story" />
                                <livewire:story-vote.downvote-action :story="$story" />
                            </div>
                        </x-filament::section>

                        <div class="flex flex-col gap-4">
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
                    </div>
                </div>
            </div>
        </section>
    </x-container>

    <x-story.reader-controls :story="$story" />
</div>
