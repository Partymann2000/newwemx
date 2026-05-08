@props(['class' => ''])

<ul {{ $attributes->merge(['class' => 'space-y-2 font-medium '.$class]) }}>
    {{ $slot }}
</ul>
