@extends('theme::layouts.wrapper', [

])

@section('title', 'View Payment')

@php
    $payment = \App\Models\Payment::where('token', $payment)->firstOrFail();
@endphp

@section('content')
    @livewire(client_view_path('payments.livewire.view-payment'), [
        'payment' => $payment,
    ])
@endsection
