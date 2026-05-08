@props([
    'text' => null,
])

<h1 {{ $attributes->class(["text-5xl font-extrabold dark:text-white"]) }}>{{ $text ?? $slot }}</h1>
