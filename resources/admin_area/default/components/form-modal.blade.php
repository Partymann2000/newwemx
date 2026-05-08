@props([
    'id' => Str::random(8),
    'title' => '',
    'fields' => [],
    'action' => null,
    'button' => __('messages.create'),
    'cancelButton' => __('messages.cancel'),
    'submitButton' => ['text' => __('messages.create')],
    'wire_model_prefix' => null,
])

@php
    if($action) {
        $action = new $action;
        $fields = array_merge($fields, $action->fields);
    }

    if($wire_model_prefix) {
        foreach($fields as $name => $field) {
            $wireModel = $field['wire_model'] ?? $name;
            $fields[$name]['wire_model'] = $wire_model_prefix . $wireModel;
        }
    }
@endphp

@if($button)
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-{{ $id }}">
    {{ $button }}
</button>
@endif

<div class="modal modal-blur fade" id="modal-{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body">
                <x-admin::form-builder :fields="$fields" />
            </div>
            <div class="modal-footer">
                @if($cancelButton)
                <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                    {{ $cancelButton }}
                </a>
                @endif
                @if($submitButton)
                <button type="submit" class="btn btn-primary ms-auto" {!! $submitButton['attributes'] ?? '' !!}>
                    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                         stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 5l0 14"/>
                        <path d="M5 12l14 0"/>
                    </svg>
                    {{ $submitButton['text'] }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
