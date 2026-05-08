@props([
    'class' => '',
])

<div {{ $attributes->class(['card-body', $class]) }}>
    {{ $slot }}
</div>
