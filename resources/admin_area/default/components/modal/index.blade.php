@props([
    'id' => Str::random(8),
    'size' => 'lg',
])

<div {{ $attributes->class(['modal', 'modal-blur', 'fade'])->merge([
    'tabindex' => '-1',
    'role' => 'dialog',
    'aria-hidden' => 'true',
    'id' => 'modal-'. $id
    ]) }}>
    <div class="modal-dialog modal-{{ $size }}" role="document">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>
