<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Locked;

new class extends Component {
    #[Locked]
    public int $order_id;

    #[Locked]
    public string $order_status;

    public $email;

    public function inviteMember()
    {
        Order::actions()->inviteMemberAsClient([
            'order_id' => $this->order_id,
            'inviter_user_id' => auth()->id(),
            'email' => $this->email,
        ]);

        $this->redirect(route('orders.view.members', ['order' => $this->order_id]), true);
    }
}

?>

<!-- invite member drawer component -->
<x-theme::drawer id="invite-order-member-drawer" wire:ignore.self>
    <x-theme::drawer.title text="Invite Member to Order"/>
    <x-theme::drawer.close-button drawer_id="invite-order-member-drawer"/>
    <x-theme::text.p class="text-sm mb-6" text="Enter the email address of the person you want to invite to this order."/>

    @if($order_status == 'terminated')
        <x-theme::alert.danger text="This order has been terminated." style="background: #00000014;"/>
    @else
        <div class="mb-4">
            <div class="mb-3">
                <x-theme::form.label for="email" text="Email"/>
                <x-theme::form.input type="email" wire:model="email" id="email" placeholder="Email"/>
                @error('email')
                    <x-theme::form.error :text="$message"/>
                @else
                    <x-theme::form.description text="The email address of the person you want to invite to the order."/>
                @enderror
            </div>
        </div>

        <x-theme::button.primary wire:click="inviteMember" class="w-full" text="Invite Member"/>
    @endif
</x-theme::drawer>
