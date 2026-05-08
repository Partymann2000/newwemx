@extends('theme::auth.wrapper')

@section('content')
    @livewire(client_view_path('auth.livewire.login-form'))
@endsection
