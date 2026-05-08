@props([
    'text' => null,
])

<label {{ $attributes->class(["block mb-2 text-sm font-medium text-gray-900 dark:text-white"])->merge([]) }}>{{ $text ?? $slot }}</label>
