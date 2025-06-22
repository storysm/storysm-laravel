@php
    $storyComment = $getRecord();
@endphp

{{-- Card Container --}}
<div {{ $attributes->merge($getExtraAttributes()) }}>
    <livewire:story-comment.story-comment-card :$storyComment />
</div>
