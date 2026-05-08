@extends('admin::layouts.wrapper', [
    'activePage' => 'roles',
])

@section('title', 'Create Role')

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('roles.livewire.create-role-form'))
    </div>
@endsection
