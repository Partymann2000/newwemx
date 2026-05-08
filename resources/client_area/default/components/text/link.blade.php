@props([
    'text' => null,
])

<a {{ $attributes->class(["font-medium text-primary-600 dark:text-primary-500 hover:underline"]) }}>{{ $text ?? $slot }}</a>
