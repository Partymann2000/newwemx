@props([
    'text' => null,
])

<h2 {{ $attributes->class(["text-4xl font-bold dark:text-white"]) }}>{{ $text ?? $slot }}</h2>

