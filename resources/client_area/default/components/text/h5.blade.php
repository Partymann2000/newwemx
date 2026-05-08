@props([
    'text' => null,
])

<h5 {{ $attributes->class(["text-xl font-bold dark:text-white"]) }}>{{ $text ?? $slot }}</h5>
