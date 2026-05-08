@extends('admin::layouts.wrapper', [
    'activePage' => 'gateways',
])

@section('title', __('messages.gateways'))

@section('content')
    @livewire(admin_view_path('gateways.livewire.edit-gateway-form'), ['gatewayConfig' => $gateway])
@endsection
