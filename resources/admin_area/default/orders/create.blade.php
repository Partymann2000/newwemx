@extends('admin::layouts.wrapper', [
    'activePage' => 'create_order',
])

@section('title', 'Create Order'))

@section('content')
    @livewire(admin_view_path('orders.livewire.create-order-form'))
@endsection
