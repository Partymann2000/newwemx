<?php

use App\Models\Category;
use App\Models\Currency;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\View\View;

new class extends Component {
    public $currencyObject;

    public $currency;

    public $display_name;

    public $market_rate;

    public $manual_rate = 0;

    public $use_manual_rate = false;

    public $is_active = true;

    public function mount($currencyObject)
    {
        $this->currencyObject = $currencyObject;
        $this->currency = $currencyObject->currency;
        $this->display_name = $currencyObject->display_name;
        $this->manual_rate = $currencyObject->manual_rate ?? 0;
        $this->use_manual_rate = $currencyObject->use_manual_rate ?? false;
        $this->is_active = $currencyObject->is_active ?? true;
    }

    public function updateCurrency()
    {
        $currency = Currency::actions()->updateCurrencyAsAdmin([
            'currency' => $this->currency,
            'display_name' => $this->display_name,
            'manual_rate' => $this->manual_rate ?? null,
            'use_manual_rate' => $this->use_manual_rate,
            'is_active' => $this->is_active,
        ]);
    }

    public function rendering(View $view): void
    {
        if ($this->currency) {
            $this->market_rate = Currency::actions()->getRateFor($this->currency);
        }
    }
}

?>
<form class="card" wire:submit="updateCurrency()">
    <div class="card-header">
        <h3 class="card-title">Update Currency</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="currency-input">{{ __('messages.currency') }}</label>
            <div class="col">
                <input type="text" wire:model.change="currency"
                       class="form-control @error('currency') is-invalid @enderror" aria-describedby="currency-input"
                       id="currency-input" placeholder="Currency Code">
                @error('currency')
                    <x-admin::form.error :message="$message"/>
                @else
                    <small class="form-hint">The 3-letter code of the currency i.e USD, EUR, GBP</small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label required"
                   for="display_name-input">{{ __('messages.display_name') }}</label>
            <div class="col">
                <input type="text" wire:model="display_name"
                       class="form-control @error('display_name') is-invalid @enderror"
                       aria-describedby="display_name-input" id="display_name-input" placeholder="Display Name">
                @error('display_name')
                <x-admin::form.error :message="$message"/>
                @else
                    <small class="form-hint">The display name of the currency i.e United States Dollar</small>
                    @enderror
            </div>
        </div>
        @if(!$use_manual_rate)
            <div class="mb-3 row">
                <label class="col-3 col-form-label required" for="usd_rate-input">{{ __('messages.usd_rate') }}</label>
                <div class="col">
                    <input type="text" wire:model="market_rate" disabled
                           class="form-control @error('market_rate') is-invalid @enderror"
                           aria-describedby="usd_rate-input"
                           id="usd_rate-input" placeholder="USD Rate">
                    @error('market_rate')
                    <x-admin::form.error :message="$message"/>
                    @else
                        <small class="form-hint mb-2">The rate of the currency against the United States Dollar
                            (USD)</small>
                        @enderror
                </div>
            </div>
        @endif
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="usd_rate-input">{{ __('messages.manual_rate') }}</label>
            <div class="col">
                <label class="form-check form-switch">
                    <input class="form-check-input" wire:model.change="use_manual_rate" type="checkbox" value="1">
                    <span class="form-check-label">Set manual currency rate</span>
                </label>
                @error('market_rate')
                <x-admin::form.error :message="$message"/>
                @else
                    <small class="form-hint mb-2">Manually set the rate of the currency against the USD</small>
                    @enderror
            </div>
        </div>
        @if($use_manual_rate)
            <div class="mb-3 row">
                <label class="col-3 col-form-label required"
                       for="usd_rate-input">{{ __('messages.manual_rate') }}</label>
                <div class="col">
                    <input type="text" wire:model.change="manual_rate"
                           class="form-control @error('manual_rate') is-invalid @enderror"
                           aria-describedby="usd_rate-input"
                           id="usd_rate-input" placeholder="Manual USD Rate" required>
                    @error('manual_rate')
                    <x-admin::form.error :message="$message"/>
                    @else
                        <small class="form-hint mb-2">The rate of the currency against the United States Dollar
                            (USD)</small>
                        <a href="https://www.google.com/finance/quote/USD-{{ $currency }}" target="_blank"
                           class="btn btn-primary btn-sm">Lookup
                            Rate</a>
                        @enderror
                </div>
            </div>
        @endif
        <div class="mb-3 row">
            <label class="col-3 col-form-label required"
                   for="usd_rate-input">Enabled</label>
            <div class="col">
                <label class="form-check form-switch">
                    <input class="form-check-input" wire:model.change="is_active" type="checkbox" value="1">
                    <span class="form-check-label">Is this currency enabled?</span>
                </label>
                @error('is_active')
                    <x-admin::form.error :message="$message"/>
                @enderror
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</form>
