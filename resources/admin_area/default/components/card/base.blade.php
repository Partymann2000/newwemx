@props([
    'class' => '',
])

<div {{ $attributes->class(['card', $class]) }}>
    {{ $slot }}
</div>
