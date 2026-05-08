@props([
    'title' => '',
    'fields' => [],
])

<div>
    @if($title)
        <h2>{!! $title !!}</h2>
    @endif

    <div class="row">
        @foreach($fields as $name => $field)
            <div class="mb-3 {{ $field['class'] ?? '' }}">
                <x-admin::form.label :label="$field['label']" :for="$name" />
                @if(in_array($field['type'], ['select']))
                    <x-admin::form.select :name="$name" :id="$name" :options="$field['options'] ?? []" :value="$field['value'] ?? ''" :multiple="$field['multiple'] ?? false" />
                @elseif(in_array($field['type'], ['checkbox' , 'radio']))
                    <x-admin::form.checkbox :name="$name" :id="$name" :description="$field['description'] ?? ''" />
                @else
                    <x-admin::form.input :name="$name" wire:model="{{ $field['wire_model'] ?? '' }}" :id="$name" :type="$field['type'] ?? 'text'" :placeholder="$field['placeholder'] ?? $field['label']" :value="$field['value'] ?? ''" />
                @endif
                @error($name)
                    <x-admin::form.error :message="$message" />
                @enderror
            </div>
        @endforeach
    </div>
</div>
