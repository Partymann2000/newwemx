@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->class(['card-header']) }}>
    @if($title || $subtitle)
        <div>
            @if($title)
                <h3 class="card-title mb-0">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <div class="text-secondary mt-1">{{ $subtitle }}</div>
            @endif
        </div>
    @endif

    @if(trim((string) $slot) !== '')
        <div class="card-actions">
            {{ $slot }}
        </div>
    @endif
</div>
