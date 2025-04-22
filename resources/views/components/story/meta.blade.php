@props(['icon', 'iconClass' => 'size-3'])

<div {{ $attributes->merge(['class' => 'flex flex-row items-center gap-1 text-gray-600 dark:text-gray-500']) }}>
    <x-filament::icon class="{{ $iconClass }}" :icon="$icon" />
    {{ $slot }}
</div>
