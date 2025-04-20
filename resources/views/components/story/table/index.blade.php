@php
    $record = $getRecord();
@endphp

{{-- Card Container --}}
<div
    {{ $attributes->merge($getExtraAttributes())->class(['overflow-hidden rounded-xl aspect-[2] flex -m-4 hover:bg-gray-50 dark:hover:bg-white/5']) }}>

    {{-- Use flex to create two columns within the card --}}
    <div class="flex w-full h-full">

        {{-- Left: Cover Media (1/3 width) --}}
        <div class="flex-shrink-0 overflow-hidden basis-1/3">
            @if ($record->coverMedia)
                <img src="{{ $record->coverMedia->url }}" alt="{{ $record->coverMedia->alt ?? 'Story Cover' }}"
                    {{-- Use
                    ->url directly --}} class="object-cover w-full h-full">
            @else
                {{-- Placeholder if no cover media --}}
                <div class="flex items-center justify-center w-full h-full text-sm">
                    {{ __('story.resource.no_cover_media') }}
                </div>
            @endif
        </div>

        {{-- Right: Content (2/3 width) --}}
        <div class="flex flex-col justify-between p-2 space-y-1 basis-2/3">
            {{-- Title --}}
            <p class="text-lg font-semibold line-clamp-1" title="{{ $record->title }}">
                {{ $record->title }}
            </p>

            {{-- Creator --}}
            <x-story.meta icon="heroicon-m-user" title="{{ $record->creator->name }}">
                {{ $record->creator->name }}
            </x-story.meta>

            <span class="flex-grow"></span>

            {{-- Published Date --}}
            @if ($record->published_at)
                <x-story.meta title="{{ $record->published_at->format('Y-m-d H:i') }}" icon="heroicon-m-calendar-days">
                    {{ $record->published_at->format('Y-m-d H:i') }}
                </x-story.meta>
            @endif
        </div>
    </div>
</div>
