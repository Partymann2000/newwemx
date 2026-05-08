@extends('admin::layouts.wrapper', [
    'activePage' => 'gateways',
])

@section('title', __('messages.gateways'))

@section('content')
    @livewire(admin_view_path('gateways.livewire.create-gateway-form'))
@endsection
