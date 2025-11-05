@php
    $story = $getRecord();
@endphp

{{-- Card Container --}}
<div
    {{ $attributes->merge($getExtraAttributes())->class(['overflow-hidden rounded-xl aspect-[2] flex -m-4 hover:bg-gray-50 dark:hover:bg-white/5']) }}>

    {{-- Use flex to create two columns within the card --}}
    <div class="flex w-full h-full">

        {{-- Left: Cover Media (1/3 width) --}}
        <div class="flex-shrink-0 overflow-hidden basis-1/3">
            <a wire:navigate href="{{ route('stories.show', ['story' => $story]) }}">
                @if ($story->coverMedia)
                    <img src="{{ $story->coverMedia->url }}" alt="{{ $story->coverMedia->alt }}"
                        class="object-cover w-full h-full">
                @else
                    {{-- Placeholder if no cover media --}}
                    <div
                        class="flex flex-col items-center justify-center h-full bg-gray-300 dark:bg-gray-700 rounded-l-xl">
                        <x-filament::icon alias="story-card.cover-media" class="w-6 h-6 text-gray-500 dark:text-gray-400"
                            icon="heroicon-o-eye-slash" />
                    </div>
                @endif
            </a>
        </div>

        {{-- Right: Content (2/3 width) --}}
        <div class="flex flex-col justify-between p-2 space-y-1 basis-2/3">
            <div class="flex flex-row">
                {{-- Title --}}
                <a wire:navigate href="{{ route('stories.show', ['story' => $story]) }}">
                    <p class="text-lg font-semibold line-clamp-2" title="{{ $story->title }}">
                        {{ $story->title }}
                    </p>
                </a>
                <livewire:story.actions :class="'-mt-2 -mr-2'" :story="$story" />
            </div>

            {{-- Creator --}}
            <x-story.meta icon="heroicon-m-user" title="{{ $story->creator->name }}">
                <p class="text-sm">{{ $story->creator->name }}</p>
            </x-story.meta>

            <span class="flex-grow"></span>

            <div class="flex flex-row gap-2">
                {{-- View Count --}}
                @if ($story->view_count > 500)
                    <x-story.meta icon="heroicon-m-eye" iconClass="size-3">
                        <p class="text-xs">{{ $story->formattedViewCount() }}</p>
                    </x-story.meta>
                @endif

                {{-- StoryComment Count --}}
                <x-story.meta icon="heroicon-m-chat-bubble-oval-left-ellipsis" iconClass="size-3">
                    <p class="text-xs">{{ $story->formattedCommentCount() }}</p>
                </x-story.meta>
            </div>
        </div>
    </div>
</div>
