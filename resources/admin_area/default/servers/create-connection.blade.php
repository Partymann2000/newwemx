@extends('admin::layouts.wrapper', [
    'activePage' => 'server_connections',
])

@section('title', __('messages.server_connections'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('servers.livewire.create-connection-form'))
    </div>
@endsection
