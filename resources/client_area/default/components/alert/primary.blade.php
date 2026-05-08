@props([
    'text' => null,
])

<div {{ $attributes->class(["p-4 mb-4 text-sm text-primary-800 rounded-lg bg-primary-50 dark:bg-gray-800 dark:text-primary-400"])->merge(['role' => 'alert']) }}>{{ $text ?? $slot }}</div>
