@props([
    'type' => 'checkbox',
    'name' => null,
    'description' => null,
    'id' => null,
])

<label class="form-check">
    <input {{ $attributes->class(['form-check-input'])->merge(['type' => $type, 'name' => $name, 'id' => $id]) }}>
    <span class="form-check-label">{!! $description !!}</span>
</label>
