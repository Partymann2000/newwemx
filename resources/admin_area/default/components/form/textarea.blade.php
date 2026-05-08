@props([
    'content' => '',
])

<textarea {{ $attributes->class(['form-control']) }}>{{ $content ?? $slot }}</textarea>
