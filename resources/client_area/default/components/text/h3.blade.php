@props([
    'text' => null,
])

<h3 {{ $attributes->class(["text-3xl font-bold dark:text-white"]) }}>{{ $text ?? $slot }}</h3>

