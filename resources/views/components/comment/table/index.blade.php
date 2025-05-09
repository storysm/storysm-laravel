@php
    $comment = $getRecord();
@endphp

{{-- Card Container --}}
<div {{ $attributes->merge($getExtraAttributes()) }}>
    <x-comment.card :comment="$comment" />
</div>
