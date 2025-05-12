@php
    $storyComment = $getRecord();
@endphp

{{-- Card Container --}}
<div {{ $attributes->merge($getExtraAttributes()) }}>
    <x-story-comment.card :storyComment="$storyComment" />
</div>
