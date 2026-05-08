<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $current_password;

    public $new_email;

    public $tfa_code;

    public function updateEmail()
    {
        $this->resetErrorBag();

        User::actions()->updateEmailAddressAsClient([
            'user_id' => auth()->id(),
            'current_password' => $this->current_password,
            'new_email' => $this->new_email,
            'tfa_code' => $this->tfa_code,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Email updated! Please check your inbox to confirm the new email address.', title: 'Success');
        $this->reset(['current_password', 'new_email']);
    }
}
?>


<x-theme::card class="mb-4">
    <div class="mb-4">
        <x-theme::text.h5 text="Update Email Address" class="mb-2" />
        <x-theme::text.p text="Update your account's email address." />
    </div>
    <form wire:submit="updateEmail()" autocomplete="off">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6">
                <x-theme::form.label for="current-password" text="Current Password" class="mb-2" />
                <x-theme::form.input type="password" wire:model="current_password" name="current_password" id="current-password" placeholder="••••••••" class="block w-full" />
                @error('current_password')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::form.label for="new_email" text="New Email Address" class="mb-2" />
                <x-theme::form.input type="email" name="new_email" id="new_email" wire:model="new_email" placeholder="New Email Address" class="block w-full" />
                @error('new_email')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            @if(auth()->user()->tfa_enabled)
                <div class="col-span-6">
                    <x-theme::form.label for="tfa_code" text="2FA Code" class="mb-2" />
                    <x-theme::form.input type="text" name="tfa_code" id="tfa_code" wire:model="tfa_code" placeholder="2FA Code" class="block w-full" />
                    @error('tfa_code')
                        <x-theme::form.error :text="$message"/>
                    @enderror
                </div>
            @endif
            <div class="col-span-6">
                <x-theme::button.primary class="col-span-6" type="submit" text="Update Email Address" />
            </div>
        </div>
    </form>
</x-theme::card>
