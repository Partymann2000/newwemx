@extends('theme::auth.wrapper')

@section('content')
    <div class="flex flex-col items-center justify-center">
        <svg class="w-12 h-12 mb-4 text-primary-400 dark:text-primary-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M11.644 3.066a1 1 0 0 1 .712 0l7 2.666A1 1 0 0 1 20 6.68a17.694 17.694 0 0 1-2.023 7.98 17.406 17.406 0 0 1-5.402 6.158 1 1 0 0 1-1.15 0 17.405 17.405 0 0 1-5.403-6.157A17.695 17.695 0 0 1 4 6.68a1 1 0 0 1 .644-.949l7-2.666Zm4.014 7.187a1 1 0 0 0-1.316-1.506l-3.296 2.884-.839-.838a1 1 0 0 0-1.414 1.414l1.5 1.5a1 1 0 0 0 1.366.046l4-3.5Z" clip-rule="evenodd"/>
        </svg>
        <x-theme::text.h4 class="mb-4 text-center">Email Verified Successfully</x-theme::text.h4>
        <x-theme::text.p class="mb-8 text-center">
            Your email address has been verified successfully. You can now login to your account.
        </x-theme::text.p>
        <div class="w-full text-center">
            <x-theme::button.primary href="{{ route('login') }}" text="Login to account" class="w-full sm:py-3.5 mb-4" />
        </div>
    </div>
@endsection
