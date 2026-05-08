@extends('admin::layouts.wrapper', [
    'activePage' => 'servers',
])

@section('title', __('messages.servers'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.extensions.discover') }}" wire:navigate>{{ __('messages.reload') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    {{--  Servers Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.servers'),
        'entries' => 15,
        'columns' => [
            __('messages.identifier'),
            __('messages.name'),
            __('messages.description'),
            __('messages.version'),
            __('messages.connections'),
            __('messages.status'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.identifier'),
            __('messages.name'),
            __('messages.version'),
            __('messages.status'),
            __('messages.created_at'),
        ],
        'rows' =>\App\Models\Server::latest()->get()->map(function ($extension) {
            return [
                $extension->identifier,
                $extension->extension()->getName(),
                $extension->extension()->getDescription(),
                $extension->extension()->getVersion(),
                '<a href="' . route('admin.servers.connections') . '" class="text-primary" wire:navigate>' . \App\Models\ServerConnection::where('extension_identifier', $extension->identifier)->count() . ' ' . __('messages.connections') . '</a>',
                '<span class="badge badge-outline text-' . ($extension->status == 'enabled' ? 'green' : 'red') . '">' . ucfirst($extension->status) . '</span>',
                $extension->created_at->translatedFormat('d M Y'),
                ($extension->status != 'enabled' ?
                    '<a href="' . route('admin.extensions.toggle', $extension->identifier) . '" class="text-info" wire:navigate>Enable</a>' :
                    '<a href="' . route('admin.extensions.toggle', $extension->identifier) . '" class="text-danger" wire:navigate>Disable</a> ' 
                    . '<a href="' . route('admin.servers.connections.create', ['serverId' => $extension->identifier]) . '" class="text-primary" wire:navigate>Setup</a>'
                )
            ];
        })->toArray(),
    ])
@endsection
