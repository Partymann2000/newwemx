@extends('admin::layouts.wrapper', [
    'activePage' => 'currencies',
])

@section('title', __('messages.create_currency'))

@section('content')
    @livewire(admin_view_path('currencies.livewire.create-currency-form'))
@endsection
