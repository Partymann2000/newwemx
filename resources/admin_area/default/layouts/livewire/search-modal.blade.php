<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;

new class extends Component
{
    public string $searchQuery = '';

    public $userSearchResults = [];

    public $orderSearchResults = [];

    public $paymentSearchResults = [];

    public function updated($view)
    {
        if ($this->searchQuery !== '') {
            $this->userSearchResults = User::where('username', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('email', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('first_name', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('last_name', 'like', '%' . $this->searchQuery . '%')
                ->get();

            $this->orderSearchResults = Order::where('order_id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('external_id', 'like', '%' . $this->searchQuery . '%')
                ->get();

            $this->paymentSearchResults = Payment::where('token', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('invoice_id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('transaction_id', 'like', '%' . $this->searchQuery . '%')
                ->get();
        }
    }
}

?>

<div class="modal modal-blur fade" id="modal-search" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" wire:model.change="searchQuery" name="search-input" placeholder="Search">
                </div>
                <div class="list-group list-group-flush overflow-auto" style="max-height: 35rem">
                    @if(!empty($userSearchResults))
                    <div class="list-group-header sticky-top">Users</div>
                    @foreach($userSearchResults as $user)
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-auto">
                                <a href="{{ route('admin.users.edit', $user->id) }}">
                                    <span class="avatar avatar-1" style="background-image: url({{ $user->getAvatarUrl() }})"> </span>
                                </a>
                            </div>
                            <div class="col text-truncate">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-body d-block">{{ $user->full_name }} (#{{ $user->id }})</a>
                                <div class="text-secondary text-truncate mt-n1">
                                    {{ $user->username }} ({{ $user->email }})
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif

                    @if(!empty($orderSearchResults))
                        <div class="list-group-header sticky-top">Orders</div>
                        @foreach($orderSearchResults as $order)
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="{{ route('admin.orders.edit', $order->id) }}">
                                            <span class="avatar avatar-1" style="background-image: url({{ $order->package->icon() }})"> </span>
                                        </a>
                                    </div>
                                    <div class="col text-truncate">
                                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="text-body d-block">{{ $order->package->name }} (#{{ $order->id }})</a>
                                        <div class="text-secondary text-truncate mt-n1">
                                            {{ $order->external_id }} ({{ $order->status }})
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
