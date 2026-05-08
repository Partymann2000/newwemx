@props([
    'description' => null,
])

<small {{ $attributes->class(['form-hint']) }}>{{ $description ?? $slot }}</small>
