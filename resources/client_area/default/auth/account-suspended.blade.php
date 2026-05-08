@extends('theme::auth.wrapper')

@section('content')
    <div class="flex flex-col items-center justify-center">
        <svg class="w-12 h-12 mb-4 text-red-400 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm13.707-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293Z" clip-rule="evenodd"/>
        </svg>

        <x-theme::text.h4 class="mb-4 text-center">Account Suspended</x-theme::text.h4>
        <x-theme::text.p class="mb-4 text-center">
            Your account currently has an active suspension and access is temporarily restricted.
        </x-theme::text.p>

        @if($ban->reason)
        <x-theme::text.p class="mb-4 text-center">
            Reason: {{ $ban->reason ?: 'No reason provided.' }}
        </x-theme::text.p>
        @endif

        @if($ban->expires_at)
        <x-theme::text.p class="mb-4 text-center">
            Expires: {{ $ban->expires_at ? $ban->expires_at->format(settings('date_format', 'd M Y H:i')) : 'No expiry date set' }}
        </x-theme::text.p>
        @endif
    </div>
@endsection
