@props([
    'label' => null,
    'for' => null,
])

<label {{ $attributes->class(['form-label'])->merge(['for' => $for]) }}>
    {{ $label ?? $slot }}
</label>
