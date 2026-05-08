<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $company_name;

    public $tax_id;

    public $address;

    public $address2;

    public $country;

    public $region;

    public $city;

    public $zip_code;

    public function mount(): void
    {
        $address = auth()->user()->address;
        $this->company_name = $address->company_name;
        $this->tax_id = $address->tax_id;
        $this->address = $address->address;
        $this->address2 = $address->address2;
        $this->country = $address->country;
        $this->region = $address->region;
        $this->city = $address->city;
        $this->zip_code = $address->zip_code;
    }

    public function updateAddress()
    {
        $this->resetErrorBag();

        User::actions()->updateAddressAsClient([
            'user_id' => auth()->id(),
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'address2' => $this->address2,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Address saved!', title: 'Success');
    }
}
?>


<x-theme::card class="mb-4">
    <div class="mb-4">
        <x-theme::text.h5 text="My Address" class="mb-2" />
        <x-theme::text.p text="Update your account's address information" />
    </div>

    <form wire:submit="updateAddress()">
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6">
                <x-theme::form.label for="company_name" text="Company Name" class="mb-2" />
                <x-theme::form.input type="text" wire:model.change="company_name" name="company_name" id="company_name" placeholder="Company Name" class="block w-full" />
                @error('company_name')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            @if($company_name)
                <div class="col-span-6">
                    <x-theme::form.label for="tax_id" text="Tax ID" class="mb-2" />
                    <x-theme::form.input type="text" wire:model="tax_id" name="tax_id" id="tax_id" placeholder="Tax ID" class="block w-full" />
                    @error('tax_id')
                        <x-theme::form.error :text="$message"/>
                    @enderror
                </div>
            @endif
            <div class="col-span-6">
                <x-theme::form.label for="address" text="Address" class="mb-2" />
                <x-theme::form.input type="text" wire:model="address" name="address" id="address" placeholder="Address" class="block w-full" />
                @error('address')
                <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::form.label for="address2" text="Address 2" class="mb-2" />
                <x-theme::form.input type="text" wire:model="address2" name="address2" id="address2" placeholder="Address 2" class="block w-full" />
                @error('address2')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="country" text="Country" class="mb-2" />
                <x-theme::form.select wire:model.change="country" name="country" id="country" required placeholder="Country" class="block w-full" :options="\App\Facades\World::countries()" />
                @error('country')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="region" text="State/Province" />
                @if(in_array($country, ['US', 'CA']))
                    <x-theme::form.select id="region" wire:model.change="region" :options="\App\Facades\World::states($country)" />
                @else
                    <x-theme::form.input type="text" wire:model="region" name="region" id="region" placeholder="State/Province" class="block w-full" />
                @endif
                @error('region')
                    <x-theme::form.error :text="$message" />
                @enderror
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="city" text="City" class="mb-2" />
                <x-theme::form.input type="text" wire:model="city" name="city" id="city" placeholder="City" class="block w-full" />
                @error('city')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-theme::form.label for="zip_code" text="Zip Code" class="mb-2" />
                <x-theme::form.input type="text" wire:model="zip_code" name="zip_code" id="zip_code" placeholder="Zip Code" class="block w-full" />
                @error('zip_code')
                    <x-theme::form.error :text="$message"/>
                @enderror
            </div>
            <div class="col-span-6">
                <x-theme::button.primary class="col-span-6" text="Save Changes" />
            </div>
        </div>
    </form>
</x-theme::card>

