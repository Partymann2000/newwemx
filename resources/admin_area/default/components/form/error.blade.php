@props([
    'message' => '',
])

<p {{ $attributes->class(['text-sm', 'text-danger', 'mt-2'])->merge([]) }}>
    {{ $message ? $message : $slot }}
</p>
