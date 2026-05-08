@props([
    'title' => 'Uh oh, something went wrong!',
    'message' => null
])

<div class="alert alert-danger" role="alert">
    <h4 class="alert-title">
        {{ $title }}
    </h4>
    <div class="text-secondary">{{ $message ?? $slot }}</div>
</div>
