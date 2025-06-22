<div>
    <div class="flex flex-row items-start justify-between gap-2 text-sm">
        <div class="flex flex-row gap-4">
            {{-- Avatar --}}
            <x-filament-panels::avatar.user :user="$storyComment->creator" />
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    {{-- Creator --}}
                    <div class="flex flex-row gap-1">
                        <p class="font-extrabold">{{ $storyComment->creator?->name }}</p>
                        <span class="text-gray-500 dark:text-gray-400">&middot;</span>
                        <p class="text-gray-500 dark:text-gray-400" title="{{ $storyComment->created_at }}">
                            {{ $storyComment->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Body --}}
                    <p class="max-w-full prose dark:prose-invert">
                        {{ $storyComment->body }}
                    </p>
                </div>

                @if ($showReplyButton)
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

        {{-- Actions --}}
        @if ($showActions)
            @php
                $actions = [];
                if ($this->editActionPermitted()) {
                    $actions[] = $this->editAction;
                }
                if ($this->deleteActionPermitted()) {
                    $actions[] = $this->deleteAction;
                }
            @endphp

            @if (!empty($actions))
                <div>
                    <x-filament-actions::group :actions="$actions" />
                    <x-filament-actions::modals />
                </div>
            @endif
        @endif
    </div>
</div>
