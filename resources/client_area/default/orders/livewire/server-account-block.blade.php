<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;

new class extends Component {
    #[Locked]
    public int $order_id;

    public string $new_password = '';

    public string $new_password_confirmation = '';

    #[Computed]
    public function order()
    {
        return Order::find($this->order_id);
    }

    public function changeServerPassword()
    {
        if (!$this->order->canChangeExternalPassword()) {
            return;
        }

        Order::actions()->changeServerPasswordAsClient([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'new_password' => $this->new_password,
            'new_password_confirmation' => $this->new_password_confirmation
        ]);

        $this->reset([
            'new_password',
            'new_password_confirmation'
        ]);

        $this->dispatch('toast', type: 'success', message: 'Successfully updated server account password!', title: 'Success');
    }
}

?>

<div>
    @if($this->order->canChangeExternalPassword())
        <!-- Server Account Block -->
        <x-theme::action-card>
            <x-slot:title>
                Server Account
            </x-slot:title>

            <x-slot:description>
                Manage your server account security settings. <br>
                <span>Username: <span class="font-medium">{{ $this->order->getExternalUser()->username ?? '' }}</span></span>
            </x-slot:description>

            <x-slot:action>
                <x-theme::button.primary type="button" text="Change Password" data-drawer-target="drawer-change-password" data-drawer-show="drawer-change-password" data-drawer-placement="right" aria-controls="drawer-change-password"/>
            </x-slot:action>
        </x-theme::action-card>

        <!-- Change Password -->
        <x-theme::drawer wire:ignore.self tabindex="-1" aria-labelledby="drawer-change-password-label" id="drawer-change-password">
            <x-theme::drawer.title text="Change Server Password"/>
            <x-theme::drawer.close-button drawer_id="drawer-change-password"/>
            <x-theme::text.p class="text-sm mb-6" text="This form allows you to change the external password of your account"/>
            <div class="mb-6">
                <div class="mb-3">
                    <x-theme::form.label for="new_password" text="New Password"/>
                    <x-theme::form.input type="password" wire:model="new_password" id="new_password" placeholder="New Password"/>
                    @error('new_password')
                        <x-theme::form.error :text="$message"/>
                    @else
                        <x-theme::form.description text="The new password for your external account."/>
                    @enderror
                </div>
                <div class="mb-3">
                    <x-theme::form.label for="new_password_confirmation" text="Confirm New Password"/>
                    <x-theme::form.input type="password" wire:model="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm New Password"/>
                    @error('new_password_confirmation')
                    <x-theme::form.error :text="$message"/>
                    @enderror
                </div>
            </div>
            <x-theme::button.primary wire:click="changeServerPassword" class="w-full" text="Change Password"/>
        </x-theme::drawer>
    @endif
</div>
