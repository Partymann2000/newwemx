@props([
    'submit' => 'saveChanges',
    'title' => null,
    'saveLabel' => 'Save Changes',
])

<form wire:submit="{{ $submit }}">
    <x-admin::card.body>
        @if($title)
            <h2 class="mb-4">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </x-admin::card.body>

    <x-admin::card.footer transparent>
        <div class="btn-list justify-content-end">
            @isset($footerActions)
                {{ $footerActions }}
            @endisset
            <button type="submit" class="btn btn-primary btn-2">
                <span class="spinner-border spinner-border-sm me-2" role="status" wire:loading></span>
                {{ $saveLabel }}
            </button>
        </div>
    </x-admin::card.footer>
</form>
