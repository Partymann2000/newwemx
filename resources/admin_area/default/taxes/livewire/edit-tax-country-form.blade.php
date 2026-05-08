<?php

use Livewire\Volt\Component;

new class extends Component {

    public $country;

    public $country_code;

    public $sales_tax_name = 'VAT';

    public $sales_tax_rate = 0;

    public array $states = [
      [
          'state_code' => '',
          'sales_tax_rate' => 0,
          'sales_tax_name' => 'GST',
      ],
    ];

    public function mount($country)
    {
        $this->country = $country;
        $this->country_code = $country->country_code;
        $this->sales_tax_name = $country->sales_tax_name;
        $this->sales_tax_rate = $country->sales_tax_rate;
        $this->states = $country->states->toArray() ?? [];
    }

    public function addState()
    {
        // copy last state to add a new one
        $lastState = end($this->states);
        $this->states[] = $lastState;
    }

    public function removeState()
    {
        // remove last state if exists
        if (count($this->states) > 1) {
            array_pop($this->states);
        }
    }

    public function updateTaxCountry()
    {
        $country = \App\Models\SalesTaxCountry::actions()->updateSalesTaxCountryAsAdmin([
            'country_id' => $this->country->id,
            'sales_tax_name' => $this->sales_tax_name,
            'sales_tax_rate' => $this->sales_tax_rate,
            'states' => (in_array($this->country_code, ['US', 'CA']) ? $this->states : []),
        ]);
    }
}

?>
<form class="card" wire:submit="updateTaxCountry()">
    <div class="card-header">
        <h3 class="card-title">Edit Tax Country</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label required"
                   for="country-input">Country</label>
            <div class="col">
                <x-admin::form.input id="sales_tax_name-input" value="{{ \App\Facades\World::countries()[$country->country_code] }}" disabled class="mb-2 disabled" />
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label required"
                   for="sales_tax_name-input">Sales Tax Name</label>
            <div class="col">
                <x-admin::form.input wire:model="sales_tax_name" id="sales_tax_name-input" class="mb-2" />
                @error('sales_tax_name')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label required"
                   for="sales_tax_rate-input">Sales Tax Rate %</label>
            <div class="col">
                <x-admin::form.input wire:model="sales_tax_rate" id="sales_tax_rate-input" class="mb-2" />
                @error('sales_tax_rate')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>
        </div>
        @if(in_array($this->country_code, ['US', 'CA']))
        <div class="mb-3 row">
            <label class="col-3 col-form-label required">States</label>
            <div class="col">
                @foreach($states as $index => $state)
                    <div class="mb-3 row">
                        <div class="col-3">
                            <x-admin::form.label for="state_code_{{ $index }}" label="State Code" />
                            <x-admin::form.input wire:model="states.{{ $index }}.state_code" id="state_code_{{ $index }}" class="mb-2" placeholder="State Code" />
                            @error('states.'.$index.'.state_code')
                                <x-admin::form.error :message="$message"/>
                            @enderror
                        </div>
                        <div class="col-3">
                            <x-admin::form.label for="state_code_{{ $index }}" label="Combined Sales Tax Rate" />
                            <x-admin::form.input wire:model="states.{{ $index }}.sales_tax_rate" id="state_sales_tax_rate_{{ $index }}" class="mb-2" placeholder="Sales Tax Rate" />
                            @error('states.'.$index.'.sales_tax_rate')
                                <x-admin::form.error :message="$message"/>
                            @enderror
                        </div>
                        <div class="col-3">
                            <x-admin::form.label for="state_code_{{ $index }}" label="Tax Name" />
                            <x-admin::form.input wire:model="states.{{ $index }}.sales_tax_name" id="state_sales_tax_name_{{ $index }}" class="mb-2" placeholder="Sales Tax Name" />
                            @error('states.'.$index.'.sales_tax_name')
                                <x-admin::form.error :message="$message"/>
                            @enderror
                        </div>
                    </div>
                @endforeach
                <div class="">
                    <button type="button" class="btn btn-primary" wire:click="addState()">Add State</button>
                    <button type="button" class="btn btn-danger ms-2" wire:click="removeState()">Remove State</button>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</form>
