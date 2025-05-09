@props(['comment', 'showReplies' => true])

<div>
    <div {{ $attributes->merge(['class' => 'flex flex-row gap-4 rounded-xl']) }}>
        {{-- Avatar --}}
        <x-filament-panels::avatar.user :user="$comment->creator" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                {{-- Creator --}}
                <div class="flex items-center gap-2 text-sm">
                    <p class="font-extrabold">{{ $comment->creator->name }}</p>
                    <span class="text-gray-500 dark:text-gray-400">&middot;</span>
                    <p class="text-gray-500 dark:text-gray-400" title="{{ $comment->created_at }}">
                        {{ $comment->created_at->diffForHumans() }}</p>
                </div>

                {{-- Body --}}
                <p class="max-w-full prose dark:prose-invert">
                    {{ $comment->body }}
                </p>
            </div>

            @if ($showReplies)
                <div>
                    {{-- Replies --}}
                    <x-filament::button :href="route('comments.show', $comment)" icon="heroicon-m-arrow-uturn-left" outlined size="xs"
                        tag="a">
                        {{ $comment->formattedReplyCount() }}
                    </x-filament::button>
                </div>
            @endif
        </div>
    </div>
</div>
