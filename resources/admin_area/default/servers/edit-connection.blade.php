@extends('admin::layouts.wrapper', [
    'activePage' => 'server_connections',
])

@section('title', $connection->alias)

@php
    $status = $connection->status;

    if($status == 'healthy') {
        $statusColor = 'green';
    } elseif($status == 'unavailable') {
        $statusColor = 'red';
    } else {
        $statusColor = 'yellow';
    }
@endphp

@section('content')
    <div class="row g-3 align-items-center mb-3">
        <div class="col-auto">
                <span class="status-indicator status-{{ $statusColor }} status-indicator-animated">
                  <span class="status-indicator-circle"></span>
                  <span class="status-indicator-circle"></span>
                  <span class="status-indicator-circle"></span>
                </span>
        </div>
        <div class="col">
            <h2 class="page-title">{{ $connection->alias }}</h2>
            <div class="text-secondary">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item"><span class="text-{{ $statusColor }}">{{ ucfirst($status) }}</span></li>
                    <li class="list-inline-item">Last checked <span class="text-secondary">{{ $connection->last_checked_at ? $connection->last_checked_at->diffForHumans() : 'Never' }}</span> ({{ $connection->last_checked_at ? $connection->last_checked_at?->format('d M Y') : 'Never' }})</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12">
        @livewire(admin_view_path('servers.livewire.edit-connection-form'), [
            'connection' => $connection,
        ])
    </div>
@endsection
