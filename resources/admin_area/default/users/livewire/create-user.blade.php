<?php

use Livewire\Volt\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\User;

new class extends Component
{
    public $first_name;

    public $last_name;

    public $username;

    public $email;

    public $password;

    public $lang = 'en';

    public $avatar;

    public $verify_email = false;

    public $company_name;

    public $tax_id;

    public $address;

    public $address2;

    public $country;

    public $region;

    public $city;

    public $zip_code;

    public function createUser()
    {
        $user = User::actions()->createUserAsAdmin([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'lang' => $this->lang,
            'avatar' => $this->avatar,
            'verify_email' => $this->verify_email,
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'address2' => $this->address2,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
        ]);

        $this->redirect(route('admin.users.edit', ['user' => $user->id]), true);
    }

    public function generateRandomUser()
    {
        $this->first_name = fake()->firstName();
        $this->last_name = fake()->lastName();
        $this->username = Str::slug($this->first_name, '_');
        $this->email = fake()->unique()->safeEmail();
        $this->lang = 'en';
        $this->verify_email = fake()->boolean(50); // 50% chance to verify email
        $this->avatar = 'https://api.dicebear.com/7.x/identicon/svg?seed=' . $this->email;

        $this->company_name = fake()->company();
        $this->address = fake()->streetAddress();
        $this->address2 = fake()->optional()->secondaryAddress(); // Optional address 2
        $this->country = 'US';
        $this->region = fake()->state();
        $this->city = fake()->city();
        $this->zip_code = fake()->postcode();
    }

    public function rendering(View $view)
    {
        if($this->first_name AND !$this->username) {
            $this->username = Str::slug($this->first_name, '_');
        }
    }
}

?>

<div>
    <form class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.create_customer') }}</h3>
            <div class="card-options">
                <button type="button" wire:click="generateRandomUser()" class="btn btn-secondary btn-icon" wire:confirm="Are you sure you want to generate a random user?">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-dice-6"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><circle cx="8.5" cy="7.5" r=".5" fill="currentColor" /><circle cx="15.5" cy="7.5" r=".5" fill="currentColor" /><circle cx="8.5" cy="12" r=".5" fill="currentColor" /><circle cx="15.5" cy="12" r=".5" fill="currentColor" /><circle cx="15.5" cy="16.5" r=".5" fill="currentColor" /><circle cx="8.5" cy="16.5" r=".5" fill="currentColor" /></svg>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 col-md-6 mb-3">
                    <x-admin::form.label>{{ __('messages.first_name') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model.change="first_name" name="first_name" placeholder="{{ __('messages.first_name') }}" required />
                    @error('first_name')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <x-admin::form.label>{{ __('messages.last_name') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="last_name" name="last_name" placeholder="{{ __('messages.last_name') }}" />
                    @error('last_name')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.username') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="username" name="username" placeholder="{{ __('messages.username') }}" required />
                    @error('username')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.email') }}</x-admin::form.label>
                    <x-admin::form.input type="email" wire:model="email" name="email" placeholder="{{ __('messages.email') }}" required />
                    @error('email')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.password') }}</x-admin::form.label>
                    <x-admin::form.input type="password" wire:model="password" name="password" placeholder="{{ __('messages.password') }}" />
                    @error('password')
                        <x-admin::form.error :message="$message" />
                    @else
                        <small class="form-hint">{{ __('messages.leave_empty_to_generate_random_password') }}</small>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.verify_email_address') }}</x-admin::form.label>
                    <x-admin::form.checkbox name="verify_email" wire:model="verify_email" id="verify_email" description="When checked, the customers email address will be verified automatically" />
                </div>
            </div>
            <hr>
            <h3 class="card-title mb-4">{{ __('messages.address') }} {{ __('messages.optional') }}</h3>
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.company_name') }} {{ __('messages.optional') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model.change="company_name" name="company_name" placeholder="{{ __('messages.company_name') }}" />
                    @error('company_name')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                @if($company_name)
                    <div class="col-12 mb-3">
                        <x-admin::form.label>{{ __('messages.tax_id') }} {{ __('messages.optional') }}</x-admin::form.label>
                        <x-admin::form.input type="text" wire:model="tax_id" name="tax_id" placeholder="{{ __('messages.tax_id') }}" />
                        @error('tax_id')
                            <x-admin::form.error :message="$message" />
                        @else
                            <small class="form-hint">{{ __('messages.tax_id_field_desc') }}</small>
                        @enderror
                    </div>
                @endif
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.address') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="address" name="address" placeholder="{{ __('messages.address') }}" />
                    @error('address')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <x-admin::form.label>{{ __('messages.address') }} 2</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="address2" name="address2" placeholder="{{ __('messages.address') }} 2" />
                    @error('address2')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <x-admin::form.label>{{ __('messages.country') }}</x-admin::form.label>
                    <x-admin::form.select name="country" wire:model="country" :options="\App\Facades\World::countries()" />
                    @error('country')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <x-admin::form.label>{{ __('messages.region_state_province') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="region" name="region" placeholder="{{ __('messages.region_state_province') }}" />
                    @error('region')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <x-admin::form.label>{{ __('messages.city') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="city" name="city" placeholder="{{ __('messages.city') }}" />
                    @error('city')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <x-admin::form.label>{{ __('messages.zip_code') }}</x-admin::form.label>
                    <x-admin::form.input type="text" wire:model="zip_code" name="zip_code" placeholder="{{ __('messages.zip_code') }}" />
                    @error('zip_code')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="button" wire:click="createUser()" class="btn btn-primary">{{ __('messages.create') }}</button>
        </div>
    </form>
</div>
