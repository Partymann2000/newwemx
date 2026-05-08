@props([
    'title' => 'That was successful!',
    'message' => null
])

<div class="alert alert-success" role="alert">
    <h4 class="alert-title">
        {{ $title }}
    </h4>
    <div class="text-secondary">{{ $message ?? $slot }}</div>
</div>
