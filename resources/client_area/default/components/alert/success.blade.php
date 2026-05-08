@props([
    'text' => null,
])

<div {{ $attributes->class(["p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"])->merge(['role' => 'alert']) }}>{{ $text ?? $slot }}</div>
