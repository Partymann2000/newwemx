<?php

use Livewire\Volt\Component;

new class extends Component
{
    // due to a global middleware checking for email verification, we can't use livewire methods as they call api in the background
}

?>

@php
    $verificationEmailsSent = auth()->user()->emails()->where('identifier', 'email_verification')->latest();
    $lastVerificationEmail = $verificationEmailsSent->first();
    $cooldown = 120; // cooldown in seconds
    $lastSentAt = optional($lastVerificationEmail?->created_at)->timestamp ?? 0;
    $now = now()->timestamp;
    $remaining = max(0, $cooldown - ($now - $lastSentAt));
@endphp


<div class="flex flex-col items-center justify-center">
    <svg class="w-12 h-12 mb-4 text-primary-400 dark:text-primary-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
        <path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
    </svg>

    <x-theme::text.h4 class="mb-4">Verify your Email Address</x-theme::text.h4>
    <x-theme::text.p class="mb-4 text-center">Please click the link in the email to verify your account. If you don't see the email, please check your spam folder.</x-theme::text.p>
    <x-theme::text.p class="mb-4 text-center">We've emailed you a verification link to <span class="dark:text-white text-gray-900">{{ auth()->user()->email }}</span></x-theme::text.p>
    @if($verificationEmailsSent->count() <= 6)
    <div class="mb-4 text-center">
        <x-theme::text.p class="text-sm">
            Didn't receive the email?
            <span id="resend-wrapper">
            @if ($remaining > 0)
            <span class="text-gray-500 dark:text-gray-400">
                Please wait <span id="cooldown">{{ $remaining }}</span>s
            </span>
            @else
                <x-theme::text.link id="resend-button" href="{{ route('resend-verify-email') }}" wire:navigate>Resend Email</x-theme::text.link>
            @endif
        </span>
        </x-theme::text.p>
    </div>
    @endif
    <x-theme::text.link href="{{ route('logout') }}" onclick="return confirm('Are you sure?');" class="dark:text-red-500 text-red-600">Logout</x-theme::text.link>
</div>

<script>
    document.addEventListener('livewire:navigated', (event) => {
        const cooldownSpan = document.getElementById('cooldown');
        const resendWrapper = document.getElementById('resend-wrapper');

        if (!cooldownSpan) return;

        let seconds = parseInt(cooldownSpan.textContent);

        const interval = setInterval(() => {
            seconds--;

            if (seconds <= 0) {
                clearInterval(interval);

                // Replace countdown with button
                resendWrapper.innerHTML = `
                    <a id="resend-button"
                       href="{{ route('resend-verify-email') }}" wire:navigate
                       class="text-primary-500 hover:underline dark:text-primary-400"
                    >Resend Email</a>
                `;
            } else {
                cooldownSpan.textContent = seconds;
            }
        }, 1000);
    });
</script>
