@props([
    'text' => null,
])

<p {{ $attributes->class(["mt-2 text-sm text-gray-500 dark:text-gray-400"])->merge([]) }}>{{ $text ?? $slot }}</p>
