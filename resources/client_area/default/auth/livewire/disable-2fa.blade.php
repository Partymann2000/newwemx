<?php

use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {

    public $tfa_code;

    public function disableTwoFactorAuth()
    {
        User::authActions()->disableTwoFactorAuthAsClient([
            'user_id' => auth()->id(),
            'tfa_code' => $this->tfa_code,
        ]);

        session()->forget('tfa_passed_at');

        $this->redirect(route('account.settings'), true);
    }
};

?>


<div class="flex flex-col items-center justify-center">
    <x-theme::text.h4 class="mb-4">Disable Two Factor Authentication</x-theme::text.h4>
    <x-theme::text.p class="mb-4 text-center">
        To disable two-factor authentication, please enter the 2FA code from your authenticator app.
    </x-theme::text.p>
    <form wire:submit="disableTwoFactorAuth" class="w-full">
        <div class="mb-6 w-full">
            <x-theme::form.input type="text" placeholder="123456" wire:model="tfa_code" autocomplete="one-time-code"/>
            @error('tfa_code')
                <x-theme::form.error :text="$message"/>
            @enderror
        </div>
        <x-theme::button.primary type="submit" text="Disable" class="mb-3 w-full"/>
    </form>
    <x-theme::text.link href="{{ route('dashboard') }}" class="dark:text-primary-500 text-primary-600">Return to
        Dashboard
    </x-theme::text.link>
</div>
