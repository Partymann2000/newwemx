@props([
    'text' => null,
])

<span {{ $attributes->class('bg-primary-100 text-primary-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-primary-900 dark:text-primary-300')->merge([]) }}>{{ $text ?? $slot }}</span>
