<div>
    @if ($showButton)
        <!-- Button to open a modal window -->
        <x-admin::button
            data-bs-toggle="modal"
            data-bs-target="#{{ $fileName }}"
            @class($class ?? 'btn-primary')
        >
            {{ $buttonText ?? '' }}
        </x-admin::button>

        <!-- Modal window -->
        <div class="modal fade" id="{{ $fileName }}" tabindex="-1" aria-labelledby="{{ $fileName }}Label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $fileName }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="{{ __('messages.close') }}"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        @if ($content)
                            <pre class="p-3 rounded border border-gray-300"
                                 style="white-space: pre-wrap; word-wrap: break-word; overflow-x: auto;">{{ $content }}</pre>
                        @else
                            <div class="text-muted">{{ __('messages.no_content') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
