@props([
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'invalid' => false,
])

<input {{ $attributes->class(['form-control', $invalid ? 'is-invalid' : '' ])->merge([
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'placeholder' => $placeholder,
        ]) }}
/>
