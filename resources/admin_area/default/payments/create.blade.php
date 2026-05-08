@extends('admin::layouts.wrapper', [
    'activePage' => 'create_payment',
])

@section('title', 'Create Payment'))

@section('content')
    @livewire(admin_view_path('payments.livewire.create-payment-form'))
@endsection
