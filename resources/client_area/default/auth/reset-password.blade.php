@extends('theme::auth.wrapper')

@section('content')
    @livewire(client_view_path('auth.livewire.reset-password-form'), ['token' => $token->token])
@endsection
