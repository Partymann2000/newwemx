@extends('admin::layouts.wrapper', [
    'activePage' => 'packages',
])

@section('title', __('messages.edit_package'))

@section('content')
    @livewire(admin_view_path('packages.livewire.edit-package'), ['package' => $package])
@endsection
