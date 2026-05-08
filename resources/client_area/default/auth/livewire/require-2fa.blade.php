<?php

use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {

    public $showLosAccessForm = false;

    public $tfa_code;

    public $current_password;

    public function enterTwoFactorAuth()
    {
        $this->resetErrorBag();

        $response = User::authActions()->checkTwoFactorAuthAsClient([
            'user_id' => auth()->id(),
            'tfa_code' => $this->tfa_code,
        ]);

        if ($response === true) {
            // put in session that 2FA was passed
            session(['tfa_passed_at' => now()]);

            // if session has tfa_redirect_to, redirect there
            if (session()->has('tfa_redirect_to')) {
                $redirectTo = session('tfa_redirect_to');
                session()->forget('tfa_redirect_to');
            }

            $this->dispatch('toast', type: 'success', message: 'Two Factor Authentication passed!', title: 'Success');

            $this->redirect($redirectTo ?? route('dashboard'));
        }
    }

    public function lostAccessTfa()
    {
        $this->resetErrorBag();
        User::authActions()->requestDisabelmentTfaAsClient([
            'user_id' => auth()->id(),
            'current_password' => $this->current_password,
        ]);

        $this->reset('current_password');
        $this->dispatch('toast', type: 'success', message: 'Please check your email inbox for instructions', title: 'Success');
    }

    public function toggleLostAccessForm()
    {
        $this->showLosAccessForm = !$this->showLosAccessForm;
    }
};

?>


<div class="flex flex-col items-center justify-center">
    @if(!$this->showLosAccessForm)
        <x-theme::text.h4 class="mb-4">Two Factor Authentication</x-theme::text.h4>
        <x-theme::text.p class="mb-4 text-center">
            Please enter the code from your authenticator app to continue.
        </x-theme::text.p>
        <form wire:submit="enterTwoFactorAuth" class="w-full">
            <div class="mb-6 w-full">
                <x-theme::form.input type="text" placeholder="123456" wire:model="tfa_code" autocomplete="one-time-code" />
                @error('tfa_code')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <x-theme::button.primary type="submit" text="Continue" class="mb-3 w-full"/>
        </form>
        <x-theme::text.link href="#" wire:click="toggleLostAccessForm" class="dark:text-primary-500 text-primary-600">Lost access?</x-theme::text.link>
    @else
        <x-theme::text.h4 class="mb-4">Lost Access?</x-theme::text.h4>
        <x-theme::text.p class="mb-4 text-center">
            If you have lost access to your authenticator app, provide your current account password. We'll send you an email with instructions to disable two-factor authentication.
        </x-theme::text.p>
        <div class="mb-6 w-full">
            <x-theme::form.label for="current_password" text="Current Password" />
            <x-theme::form.input type="password" id="current_password" placeholder="Current Password" wire:model="current_password"/>
            @error('current_password')
                <x-theme::form.error :text="$message"/>
            @enderror
        </div>
        <x-theme::button.primary text="Continue" wire:click="lostAccessTfa" class="mb-3"/>
        <x-theme::text.link href="#" wire:click="toggleLostAccessForm" class="dark:text-primary-500 text-primary-600">Back to Two Factor Authentication</x-theme::text.link>
    @endif
</div>
