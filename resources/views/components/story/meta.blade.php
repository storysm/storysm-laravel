@props(['icon'])

<div {{ $attributes->merge(['class' => 'flex flex-row items-center gap-1 text-gray-600 dark:text-gray-500']) }}>
    <x-filament::icon class="size-3" :icon="$icon" />
    <p class="text-sm">
        {{ $slot }}
    </p>
</div>
