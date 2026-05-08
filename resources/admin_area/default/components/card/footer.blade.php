@props([
    'transparent' => false,
    'class' => '',
])

<div {{ $attributes->class([
    'card-footer',
    'bg-transparent mt-auto' => $transparent,
    $class,
]) }}>
    {{ $slot }}
</div>
