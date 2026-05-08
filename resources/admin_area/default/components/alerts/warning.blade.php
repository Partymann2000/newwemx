@props([
    'title' => 'Heads up! Something might be wrong!',
    'message' => null
])

<div class="alert alert-warning" role="alert">
    <h4 class="alert-title">
        {{ $title }}
    </h4>
    <div class="text-secondary">{!!  $message ?? $slot !!}</div>
</div>
