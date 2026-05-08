<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $user;

    public $company_name;

    public $tax_id;

    public $address;

    public $address2;

    public $country;

    public $region;

    public $city;

    public $zip_code;

    public function mount($user)
    {
        $this->user = $user;
        $address = $user->address;

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
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);

        User::actions()->updateUserAddressAsAdmin([
            'user_id' => $this->user->id,
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'address2' => $this->address2,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
        ]);

        $this->dispatch('alert', 'success', 'Address updated successfully');
        $this->dispatch('user-updated');
    }
}

?>

<div>
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.company_name') }} {{ __('messages.optional') }}
                @if($companyNameChangeCount = $user->address->getActivityLogCountForField('company_name'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'company_name']) }}">{{ $companyNameChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model.change="company_name" name="company_name" placeholder="{{ __('messages.company_name') }}" />
            @error('company_name')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        @if($company_name)
            <div class="col-12 mb-3">
                <x-admin::form.label>
                    {{ __('messages.tax_id') }} {{ __('messages.optional') }}
                    @if($taxIdChangeCount = $user->address->getActivityLogCountForField('tax_id'))
                        <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'tax_id']) }}">{{ $taxIdChangeCount }} changes</a>
                    @endif
                </x-admin::form.label>
                <x-admin::form.input type="text" wire:model="tax_id" name="tax_id" placeholder="{{ __('messages.tax_id') }}" />
                @error('tax_id')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">{{ __('messages.tax_id_field_desc') }}</small>
                @enderror
            </div>
        @endif
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.address') }}
                @if($addressChangeCount = $user->address->getActivityLogCountForField('address'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'address']) }}">{{ $addressChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="address" name="address" placeholder="{{ __('messages.address') }}" />
            @error('address')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.address') }} 2
                @if($addressTwoChangeCount = $user->address->getActivityLogCountForField('address2'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'address2']) }}">{{ $addressTwoChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="address2" name="address2" placeholder="{{ __('messages.address') }} 2" />
            @error('address2')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-admin::form.label>
                {{ __('messages.country') }}
                @if($countryChangeCount = $user->address->getActivityLogCountForField('country'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'country']) }}">{{ $countryChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.select name="country" wire:model="country" :options="\App\Facades\World::countries()" value="{{ $country }}" />
            @error('country')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-admin::form.label>
                {{ __('messages.region_state_province') }}
                @if($regionChangeCount = $user->address->getActivityLogCountForField('region'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'region']) }}">{{ $regionChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="region" name="region" placeholder="{{ __('messages.region_state_province') }}" />
            @error('region')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-admin::form.label>
                {{ __('messages.city') }}
                @if($cityChangeCount = $user->address->getActivityLogCountForField('city'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'city']) }}">{{ $cityChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="city" name="city" placeholder="{{ __('messages.city') }}" />
            @error('city')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-admin::form.label>
                {{ __('messages.zip_code') }}
                @if($zipcodeChangeCount = $user->address->getActivityLogCountForField('zip_code'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'zip_code']) }}">{{ $zipcodeChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="zip_code" name="zip_code" placeholder="{{ __('messages.zip_code') }}" />
            @error('zip_code')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
    </div>
    <div class="text-end">
        <button type="button" wire:click="updateAddress()" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</div>
