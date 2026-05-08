@props([
    'name',
    'options' => [],
    'model' => null,
    'selected' => null,
    'title' => null,
    'required' => false,
])

@if($title)
    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
@endif

<ul class="select-none grid w-full gap-4 md:grid-cols-3">
    @foreach($options as $index => $option)
        @php
            $value = data_get($option, 'value', '');
            $label = data_get($option, 'name', $value);
            $description = data_get($option, 'description');
            $iconUrl = data_get($option, 'icon_url');
            $id = $name . '-' . $index . '-' . \Illuminate\Support\Str::slug((string) $value);
        @endphp

        <li>
            <input
                type="radio"
                id="{{ $id }}"
                value="{{ $value }}"
                name="{{ $name }}"
                class="hidden peer"
                @if($model) wire:model.change="{{ $model }}" @endif
                @checked((string) $selected === (string) $value)
                @required($required)
            >
            <label
                for="{{ $id }}"
                class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 dark:peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-700 dark:hover:bg-gray-700"
            >
                <div class="block">
                    @if($iconUrl)
                        <img src="{{ $iconUrl }}" alt="{{ $label }}" class="mb-2 w-7 h-7 object-contain">
                    @endif
                    <div class="w-full font-medium mb-1">{{ $label }}</div>
                    @if($description)
                        <div class="w-full text-sm">{{ $description }}</div>
                    @endif
                </div>
            </label>
        </li>
    @endforeach
</ul>
