@extends('admin::layouts.wrapper', [
    'activePage' => 'packages',
])

@section('title', __('messages.create_package'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('packages.livewire.create-package-form'))
    </div>
@endsection
