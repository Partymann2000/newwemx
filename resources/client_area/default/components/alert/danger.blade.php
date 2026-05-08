@props([
    'text' => null,
])

<div {{ $attributes->class(["p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"])->merge(['role' => 'alert']) }}>{{ $text ?? $slot }}</div>
