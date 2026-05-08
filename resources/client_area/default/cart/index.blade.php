@extends('theme::layouts.wrapper', [

])

@section('title', 'My Cart')

@section('content')
    @livewire(client_view_path('cart.livewire.view-cart'))
@endsection
