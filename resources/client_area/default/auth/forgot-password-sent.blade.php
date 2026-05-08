@extends('theme::auth.wrapper')

@section('content')
    <div class="flex flex-col items-center justify-center">
        <svg class="w-12 h-12 mb-4 text-primary-400 dark:text-primary-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 6h-2V5h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2h-.541A5.965 5.965 0 0 1 14 10v4a1 1 0 1 1-2 0v-4c0-2.206-1.794-4-4-4-.075 0-.148.012-.22.028C7.686 6.022 7.596 6 7.5 6A4.505 4.505 0 0 0 3 10.5V16a1 1 0 0 0 1 1h7v3a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-3h5a1 1 0 0 0 1-1v-6c0-2.206-1.794-4-4-4Zm-9 8.5H7a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2Z"/>
        </svg>

        <x-theme::text.h4 class="mb-4 text-center">Password Reset Email Sent</x-theme::text.h4>
        <x-theme::text.p class="mb-8 text-center">
            If an account with that email address exists, we have sent a password reset link to <span class="dark:text-white text-gray-900">{{ request()->get('email', 'undefined') }}</span>.
        </x-theme::text.p>
        <x-theme::text.p class="mb-8 text-center">
            If you don't see the email, please check your spam folder.
        </x-theme::text.p>
        <div class="w-full text-center">
            <x-theme::button.primary href="{{ route('login') }}" text="Back to login" class="w-full sm:py-3.5 mb-4" />
        </div>
    </div>
@endsection
