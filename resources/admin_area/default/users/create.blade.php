@extends('admin::layouts.wrapper', [
    'activePage' => 'users',
])

@section('title', __('messages.create_customer'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('users.livewire.create-user'))
    </div>
@endsection
