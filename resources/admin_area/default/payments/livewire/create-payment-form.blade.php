<?php

use Livewire\Volt\Component;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Currency;
use App\Models\Payment;

new class extends Component
{
    public $user_id;

    public $description;

    public $subtotal;

    public $currency = 'USD';

    public function mount()
    {
        $this->currency = settings('currency', 'USD');
    }

    public function createPayment()
    {
        $payment = Payment::actions()->createPaymentAsAdmin([
            'user_id' => $this->user_id,
            'description' => $this->description,
            'subtotal' => $this->subtotal,
            'currency' => $this->currency,
        ]);

        $this->redirect(route('admin.payments.edit', $payment->id), true);
    }
}

?>

<form class="card" wire:submit="createPayment()">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.create_payment') }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="user-input">{{ __('messages.user') }} (Optional)</label>
            <div class="col">
                <select class="form-select @error('user_id') is-invalid @enderror mb-2" wire:model="user_id" id="user-input">
                    <option value="">{{ __('messages.select') }}</option>
                    @foreach(User::all() as $user)
                        <option value="{{ $user->id }}" data-custom-properties="<span class=&quot;avatar avatar-xs&quot; style=&quot;background-image: url({{ $user->getAvatarUrl() }})&quot;></span>">{{ $user->username }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <script>
                    // create tom select
                    document.addEventListener('livewire:navigated', function () {
                        new TomSelect("#user-input",{
                            copyClassesToDropdown: false,
                            dropdownParent: 'body',
                            controlInput: '<input>',
                            render:{
                                item: function(data,escape) {
                                    if( data.customProperties ){
                                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                    }
                                    return '<div>' + escape(data.text) + '</div>';
                                },
                                option: function(data,escape){
                                    if( data.customProperties ){
                                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                    }
                                    return '<div>' + escape(data.text) + '</div>';
                                },
                            },
                        });
                    });
                </script>
                @error('user_id')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        Select the user for which you want to create the payment, this field is optional.
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="description-input">{{ __('messages.description') }}</label>
            <div class="col">
                <input type="text" wire:model="description" class="form-control @error('description') is-invalid @enderror" aria-describedby="description-input" id="description-input" placeholder="Description">
                @error('description')
                <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The description of the payment.
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="subtotal-input">{{ __('messages.amount') }}</label>
            <div class="col">
                <input type="number" wire:model="subtotal" class="form-control @error('subtotal') is-invalid @enderror" aria-describedby="subtotal-input" id="subtotal-input" placeholder="Amount">
                @error('subtotal')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The amount of the payment.
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="currency-input">{{ __('messages.currency') }}</label>
            <div class="col">
                <select class="form-select @error('currency') is-invalid @enderror" wire:model="currency" id="currency-input">
                    @foreach(Currency::all() as $currency)
                        <option value="{{ $currency->currency }}" @if($currency->currency == settings('currency', 'USD')) selected @endif>{{ $currency->display_name }}</option>
                    @endforeach
                </select>
                @error('currency')
                <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        Select the currency of the payment.
                    </small>
                @enderror
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
    </div>
</form>
