@extends('theme::layouts.wrapper', [
    'activePage' => 'categories',
])

@section('title', 'View Package')

@section('content')
    @livewire(client_view_path('packages.livewire.view-package'), ['packageSlug' => $package])
@endsection
