@extends('theme::auth.wrapper')

@section('content')
    <div class="flex flex-col items-center justify-center">
        <svg class="w-12 h-12 mb-4 text-primary-400 dark:text-primary-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
        </svg>
        @if(auth()->user()->status == 'pending')
            <x-theme::text.h4 class="mb-4 text-center">Account Pending Approval</x-theme::text.h4>
            <x-theme::text.p class="mb-8 text-center">
                Your account is currently pending approval by an administrator. You will be notified via email once your account has been approved.
            </x-theme::text.p>
            <div class="w-full text-center">
                <x-theme::button.primary href="{{ route('logout') }}" text="Logout" confirm class="w-full sm:py-3.5 mb-4" />
            </div>
        @elseif(auth()->user()->status == 'suspended')
            <x-theme::text.h4 class="mb-4 text-center">Account Suspended</x-theme::text.h4>
            <x-theme::text.p class="mb-8 text-center">
                Your account has been suspended. Please contact support for more information.
            </x-theme::text.p>
            <div class="w-full text-center">
                <x-theme::button.primary href="{{ route('logout') }}" text="Logout" confirm class="w-full sm:py-3.5 mb-4" />
            </div>
        @else
            <x-theme::text.h4 class="mb-4 text-center">Your account is active</x-theme::text.h4>
            <x-theme::text.p class="mb-8 text-center">
                Your account is already active. You can return to the dashboard.
            </x-theme::text.p>
            <div class="w-full text-center">
                <x-theme::button.primary href="{{ route('dashboard') }}" text="Return to Dashboard" class="w-full sm:py-3.5 mb-4" />
            </div>
        @endif
    </div>
@endsection
