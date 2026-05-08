@extends('admin::layouts.wrapper', [
    'activePage' => 'taxes',
])

@section('title', 'Create Tax Country')

@section('content')
    @livewire(admin_view_path('taxes.livewire.create-tax-country-form'))
@endsection
