<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $email = '';

    public function handlePasswordResetRequest()
    {
        $this->resetErrorBag();

        \App\Models\User::authActions()->requestPasswordAsClient([
            'email' => $this->email,
        ]);

        $this->redirect(route('forgot-password-sent', ['email' => $this->email]), true);
    }
}

?>


<form class="w-full max-w-md space-y-4 md:space-y-6 xl:max-w-xl" wire:submit="handlePasswordResetRequest">
    <x-theme::text.h5>Password Reset</x-theme::text.h5>

    <div class="mb-4">
        <x-theme::form.label for="email" text="Email" />
        <x-theme::form.input type="email" placeholder="Email" wire:model="email" id="email"/>
        @error('email')
        <x-theme::form.error :text="$message" />
        @enderror
    </div>

    <x-theme::button.primary type="submit" text="Request Password Reset" class="w-full justify-content-center"/>
    <x-theme::text.p class="text-sm">Remember your password?
        <x-theme::text.link href="{{ route('login') }}" wire:navigate>Sign In</x-theme::text.link>
    </x-theme::text.p>
</form>
