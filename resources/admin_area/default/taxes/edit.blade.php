@extends('admin::layouts.wrapper', [
    'activePage' => 'taxes',
])

@section('title', 'Edit Tax Country')

@section('content')
    @livewire(admin_view_path('taxes.livewire.edit-tax-country-form'), [
        'country' => $country,
    ])
@endsection
