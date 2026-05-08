@props([
    'title' => 'Just so you know...',
    'message' => null
])

<div class="alert alert-info" role="alert">
    <h4 class="alert-title">
        {{ $title }}
    </h4>
    <div class="text-secondary">{{ $message ?? $slot }}</div>
</div>
