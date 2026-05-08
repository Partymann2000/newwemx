@extends('admin::layouts.wrapper', [
    'activePage' => 'currencies',
])

@section('title', 'Edit Currency')

@section('content')
    @livewire(admin_view_path('currencies.livewire.edit-currency-form'), ['currencyObject' => $currency])
@endsection
