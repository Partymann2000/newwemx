@extends('admin::layouts.wrapper', [
    'activePage' => 'configure_emails',
])

@section('title', __('messages.configure_smtp'))

@section('content')
    <div class="alert alert-info m-0 mb-3">
        This page allows you to configure an SMTP server to send emails. You can configure other drivers outside of SMTP directly in the .env file.
        <a href="https://laravel.com/docs/11.x/mail" target="_blank" class="alert-link">Learn more</a>
    </div>

    @livewire(admin_view_path('emails.livewire.configure-smtp-form'))
@endsection
