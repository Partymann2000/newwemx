@props([

])

<div {{ $attributes->merge(['class' => 'fixed top-0 right-0 z-40 h-screen p-4 overflow-y-auto transition-transform translate-x-full bg-white w-80 dark:bg-gray-800', 'tabindex' => '-1']) }}>{{ $slot }}</div>
