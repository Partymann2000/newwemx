@props([
'stack' => true, // stacked (label above value). If false, you can still control with your own content.
])

<div {{ $attributes->merge([
    'class' => 'relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700'
    ]) }}>
    @isset($label)
    <h6 {{ $label->attributes->merge([
        'class' => 'mb-2 text-base font-medium leading-none text-gray-900 dark:text-white'
        ]) }}>
        {{ $label }}
    </h6>
    @endisset

    <div class="{{ $stack ? 'flex items-center text-gray-500 dark:text-gray-400' : '' }}">
        {{ $slot }}
    </div>
</div>
