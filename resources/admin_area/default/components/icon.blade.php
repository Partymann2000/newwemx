@props([
    'icon' => '',
    'class' => 'icon',
    'outline' => false,
])

<i {{ $attributes->class(['ti ti-' . $icon, $class, $outline ? 'icons-tabler-outline' : ''])->merge([]) }}></i>
