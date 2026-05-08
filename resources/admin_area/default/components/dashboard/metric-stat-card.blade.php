@props([
    'title',
    'value',
    'description' => null,
    'change' => 0,
    'rangeLabel' => null,
])

<x-admin::card.base>
    <x-admin::card.body>
        <div class="d-flex align-items-center">
            <div class="subheader">{{ $title }}</div>
            @if($rangeLabel || isset($actions))
                <div class="ms-auto lh-1">
                    @isset($actions)
                        {{ $actions }}
                    @else
                        <span class="text-secondary">{{ $rangeLabel }}</span>
                    @endisset
                </div>
            @endif
        </div>
        <div class="h1 mb-3">{{ $value }}</div>
        <div class="d-flex">
            <div>{{ $description }}</div>
            <div class="ms-auto">
                @if($change > 0)
                    <span class="text-green d-inline-flex align-items-center lh-1">
                        {{ $change }}%
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 17l6 -6l4 4l8 -8"></path>
                            <path d="M14 7l7 0l0 7"></path>
                        </svg>
                    </span>
                @elseif($change < 0)
                    <span class="text-red d-inline-flex align-items-center lh-1">
                        {{ $change }}%
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M3 7l6 6l4 -4l8 8"></path><path d="M21 10l0 7l-7 0"></path></svg>
                    </span>
                @else
                    <span class="text-yellow d-inline-flex align-items-center lh-1">
                        {{ $change }}%
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M5 12l14 0"></path>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </x-admin::card.body>
</x-admin::card.base>
