<button {{ $attributes->class(['btn', 'btn-primary'])->merge([
    'type' => 'button',
    'data-bs-toggle' => 'modal',
    'data-bs-target' => '#modal-' . $id,
        ]) }}>
    {{ $slot }}
</button>
