<div>
    <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-4">
            <div class="flex flex-row items-center justify-between gap-2 text-sm">
                <div class="flex flex-row gap-4">
                    {{-- Avatar --}}
                    <x-filament-panels::avatar.user :user="$storyComment->creator" />
                    {{-- Creator --}}
                    <div class="flex flex-row gap-1">
                        <p class="font-extrabold">{{ $storyComment->creator->name }}</p>
                        <span class="text-gray-500 dark:text-gray-400">&middot;</span>
                        <p class="text-gray-500 dark:text-gray-400" title="{{ $storyComment->created_at }}">
                            {{ $storyComment->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                @if ($this->editActionPermitted())
                    <div>
                        <x-filament-actions::group :actions="[$this->editAction]" />
                        <x-filament-actions::modals />
                    </div>
                @endif
            </div>

            {{-- Body --}}
            <p class="max-w-full prose dark:prose-invert">
                {{ $storyComment->body }}
            </p>
        </div>

        @if ($showReplies)
            <div>
                {{-- Replies --}}
                <x-filament::button :href="route('story-comments.show', $storyComment)" icon="heroicon-m-arrow-uturn-left" :outlined="!$hasUserReplied"
                    size="xs" tag="a">
                    {{ $storyComment->formattedReplyCount() }}
                </x-filament::button>
            </div>
        @endif
    </div>
</div>
