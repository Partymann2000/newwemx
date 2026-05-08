@props([
    'text' => null,
])

<h6 {{ $attributes->class(["text-lg font-bold dark:text-white"]) }}>{{ $text ?? $slot }}</h6>


