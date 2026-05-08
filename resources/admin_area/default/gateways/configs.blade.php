@extends('admin::layouts.wrapper', [
    'activePage' => 'gateways',
])

@section('title', __('messages.gateways'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="/admin/gateways/configs/create" wire:navigate="true" >{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    {{--  Gateways Configs Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.gateway_configs'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.display_name'),
            __('messages.gateway'),
            __('messages.type'),
            'Webhook',
            __('messages.status'),
            'Staff Only',
            __('messages.updated_at'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.display_name'),
            __('messages.gateway'),
            __('messages.type'),
            __('messages.status'),
            'Staff Only',
            __('messages.updated_at'),
            __('messages.created_at'),
        ],
        'rows' =>\App\Models\GatewayConfig::get()->map(function ($config) {
            return [
                $config->id,
                '<a href="' . route('admin.gateways.configs.edit', $config->id) . '" wire:navigate>' . $config->display_name . '</a>',
                $config->extension_identifier,
                $config->type,
                $config->webhook_id ? route('payments.gateway.webhook', $config->webhook_id) : 'Not Available',
                '<span class="badge bg-' . ($config->is_active ? 'green' : 'red') . '-lt">' . ($config->is_active ? __('messages.enabled') : __('messages.disabled')) . '</span>',
                $config->is_staff_only ? 'True' : 'False',
                $config->updated_at->translatedFormat('d M Y'),
                $config->created_at->translatedFormat('d M Y'),
                '<a href="' . route('admin.gateways.configs.edit', $config->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
@endsection
