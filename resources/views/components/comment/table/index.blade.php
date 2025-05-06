@php
    $comment = $getRecord();
@endphp

{{-- Card Container --}}
<div {{ $attributes->merge($getExtraAttributes()) }}>
    <div class="flex flex-row gap-4 rounded-xl">
        {{-- Avatar --}}
        <x-filament-panels::avatar.user :user="$comment->creator" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                {{-- Creator --}}
                <p class="text-sm font-extrabold">{{ $comment->creator->name }}</p>

                {{-- Body --}}
                <p class="max-w-full prose dark:prose-invert">
                    {{ $comment->body }}
                </p>
            </div>

            <div>
                {{-- Replies --}}
                <x-filament::button icon="heroicon-m-arrow-uturn-left" outlined size="xs">
                    {{ $comment->formattedReplyCount() }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
