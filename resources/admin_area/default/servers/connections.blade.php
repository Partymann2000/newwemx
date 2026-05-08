@extends('admin::layouts.wrapper', [
    'activePage' => 'server_connections',
])

@section('title', __('messages.server_connections'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.servers.connections.create') }}" wire:navigate>{{ __('messages.create_server_connection') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    {{--  Server Connections Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.server_connections'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.description'),
            __('messages.server'),
            __('messages.status'),
            __('messages.last_checked_at'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.server'),
            __('messages.status'),
            __('messages.last_checked_at'),
            __('messages.created_at'),
        ],
        'rows' =>\App\Models\ServerConnection::get()->map(function ($connection) {
            return [
                $connection->id,
                '<a href="' . route('admin.servers.connections.edit', $connection->id) . '" wire:navigate>' . $connection->alias . '</a>',
                $connection->short_description,
                $connection->server ? $connection->server->name : '',
                '<span class="badge badge-outline text-' . ($connection->status == 'healthy' ? 'green' : ($connection->status == 'unavailable' ? 'red' : 'yellow')) . '">' . ucfirst($connection->status) . '</span>',
                $connection->last_checked_at ? $connection->last_checked_at->diffForHumans() : '-',
                $connection->created_at->translatedFormat('d M Y'),
                '<a href="' . route('admin.servers.connections.edit', $connection->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
@endsection
