@props([
    'name' => null,
    'options' => [],
    'value' => null,
    'multiple' => false,
    'id' => Str::random(8),
    'searchable' => false,
])

<select {{ $attributes->class(['form-control'])->merge([
            'multiple' => $multiple,
            'name' => $name,
            'id' => $id,
        ]) }}>
        <option value="">Choose Option...</option>
    @foreach($options as $key => $option)
        <option value="{{ $key }}" @if(is_array($value) ? in_array($key, $value) : $key == $value) selected @endif>{{ $option }}</option>
    @endforeach
</select>

@if($searchable)
    <script>
        document.addEventListener('livewire:navigated', function () {
            const tomSelect = new TomSelect("#{{ $id }}",{
                create: false,
            });

            tomSelect.setValue(@json(is_array($value) ? $value : [$value]), true);
        });
    </script>
@endif
