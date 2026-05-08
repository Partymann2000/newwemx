@props([
    'text' => null,
])

<p {{ $attributes->class(["mt-2 text-sm text-green-600 dark:text-green-500"])->merge([]) }}>{{ $text ?? $slot }}</p>
