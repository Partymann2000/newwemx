@props([
    'text' => null,
])

<p {{ $attributes->class(["text-gray-500 dark:text-gray-400"])->merge([]) }}>{{ $text ?? $slot }}</p>
