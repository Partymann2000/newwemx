@props([
    'variant' => 'info',
    'title' => null,
    'message' => null,
    'linkText' => null,
    'linkHref' => null,
    'navigate' => true,
])

<div {{ $attributes->class(['alert', 'alert-' . $variant]) }} role="alert">
    @if($title)
        <h4 class="alert-title">{{ $title }}</h4>
    @endif

    <div class="text-secondary">
        {!! $message ?? $slot !!}
        @if($linkText && $linkHref)
            <a class="alert-link" href="{{ $linkHref }}" @if($navigate) wire:navigate @endif>{{ $linkText }}</a>
        @endif
    </div>
</div>
