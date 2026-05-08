<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $first_name;

    public $last_name;

    public $username;

    public $phone;

    public bool $subscribed;

    public function mount(): void
    {
        $user = auth()->user();
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->username = $user->username;
        $this->phone = $user->phone;
        $this->subscribed = $user->is_subscribed;
    }

    public function updateGeneralInformation()
    {
        $this->resetErrorBag();

        User::actions()->updateAccountAsClient([
            'user_id' => auth()->id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'phone' => $this->phone,
            'is_subscribed' => $this->subscribed,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Profile saved!', title: 'Success');
    }
}
?>


<x-theme::card class="mb-4">
    <div class="mb-4">
        <x-theme::text.h5 text="General Information" class="mb-2" />
        <x-theme::text.p text="Update your account's general information." />
    </div>

    <form wire:submit="updateGeneralInformation()">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="first_name" text="First Name" class="mb-2" />
                <x-theme::form.input type="text" wire:model="first_name" name="first_name" id="first_name" required placeholder="First Name" class="block w-full" />
                @error('first_name')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="last_name" text="Last Name" class="mb-2" />
                <x-theme::form.input type="text" wire:model="last_name" name="last_name" id="last_name" placeholder="Last Name" required class="block w-full" />
                @error('last_name')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::form.label for="username" text="Username" class="mb-2" />
                <x-theme::form.input type="text" wire:model="username" name="username" placeholder="Username" id="username" required class="block w-full" />
                @error('username')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::form.label for="phone" text="Phone Number" class="mb-2" />
                <x-theme::form.input type="tel" wire:model="phone" name="phone" id="phone" placeholder="Phone Number" class="block w-full" />
                @error('phone')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::form.toggle wire:model="subscribed" text="Subscribe to our newsletter" />
            </div>
            <div class="col-span-6">
                <x-theme::button.primary class="col-span-6" text="Save Changes" />
            </div>
        </div>
    </form>
</x-theme::card>
