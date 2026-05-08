@extends('admin::layouts.wrapper', [
    'activePage' => 'orders'
])

@section('title', "Viewing Order #{$order->id}")

@section('content')
    @livewire(admin_view_path('orders.livewire.edit-order'), ['order' => $order])
@endsection
