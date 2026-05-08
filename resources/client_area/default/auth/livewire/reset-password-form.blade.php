<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $token;

    public $password = '';

    public $password_confirmation = '';

    public function handlePasswordReset()
    {
        $this->resetErrorBag();

        User::authActions()->resetPasswordAsClient([
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $this->redirect(route('login'), true);
    }
}
?>


<form class="w-full max-w-md space-y-4 md:space-y-6 xl:max-w-xl" wire:submit="handlePasswordReset">
    <x-theme::text.h5>Reset Password</x-theme::text.h5>

    <div class="mb-4">
        <x-theme::form.label for="password" text="New Password" />
        <x-theme::form.input type="password" placeholder="New Password" wire:model="password" id="password"/>
        @error('password')
            <x-theme::form.error :text="$message" />
        @enderror
    </div>

    <div class="mb-4">
        <x-theme::form.label for="password_confirmation" text="Confirm New Password" />
        <x-theme::form.input type="password" placeholder="Confirm New Password" wire:model="password_confirmation" id="password_confirmation"/>
        @error('password_confirmation')
            <x-theme::form.error :text="$message" />
        @enderror
    </div>

    <x-theme::button.primary type="submit" text="Reset Password" class="w-full justify-content-center"/>
    <x-theme::text.p class="text-sm">Remember your password?
        <x-theme::text.link href="{{ route('login') }}" wire:navigate>Sign In</x-theme::text.link>
    </x-theme::text.p>
</form>
