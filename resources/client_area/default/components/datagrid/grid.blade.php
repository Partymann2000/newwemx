@props([
    'cols' => 3,   // number of columns
    'gap'  => 4,   // Tailwind gap size
])

@php
    $colsClass = 'grid-cols-' . $cols;
    $gapClass  = 'gap-' . $gap;
@endphp

<div {{ $attributes->merge([
    'class' => "mt-4 mb-4 grid {$colsClass} {$gapClass} sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-{$cols}"
]) }}>
    {{ $slot }}
</div>
