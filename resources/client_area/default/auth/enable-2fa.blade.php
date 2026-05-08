@extends('theme::auth.wrapper')

@section('content')
    @livewire(client_view_path('auth.livewire.enable-2fa'))
@endsection
