@props([
    'text' => null,
])

<h4 {{ $attributes->class(["text-2xl font-bold dark:text-white"]) }}>{{ $text ?? $slot }}</h4>

