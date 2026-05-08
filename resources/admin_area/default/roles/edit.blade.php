@extends('admin::layouts.wrapper', [
    'activePage' => 'roles',
])

@section('title', 'Edit Role')

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('roles.livewire.edit-role-form'), ['role' => $role])
    </div>
@endsection
