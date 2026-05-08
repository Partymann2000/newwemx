@props([
    'text' => null,
])

<p {{ $attributes->class(["mt-2 text-sm text-red-600 dark:text-red-500"])->merge([]) }}>{{ $text ?? $slot }}</p>
