<?php

use App\Models\Package;
use Livewire\Volt\Component;
use Illuminate\View\View;

new class extends Component {
    public $recurringTypes = [
        '0' => 'One Time',
        '1' => 'Daily',
        '7' => 'Weekly',
        '14' => 'Bi-Weekly',
        '30' => 'Monthly',
        '90' => 'Quarterly',
        '180' => 'Semi-Annually',
        '365' => 'Annually',
    ];

    public $description = '';

    public $period_in_days = 0;

    public $price = 0;

    public $setup_fee = 0;

    public $upgrade_fee = 0;

    public $package;

    public function createPackagePrice(): void
    {
        $price = Package::actions()->createPackagePriceAsAdmin([
            'short_description' => $this->description,
            'package_id' => $this->package->id,
            'period_in_days' => $this->period_in_days,
            'price' => $this->price,
            'setup_fee' => $this->setup_fee,
            'upgrade_fee' => $this->upgrade_fee,
        ]);

        $this->redirect(route('admin.packages.edit', ['package' => $this->package->id, 'page' => 'prices']), true);
    }

    public function mount($package): void
    {
        $this->package = $package;
    }
}
?>

<form wire:submit="createPackagePrice()">
    <div class="mb-3">
        <x-admin::form.label for="description" label="Description (Optional)"/>
        <x-admin::form.input type="text" wire:model="description" id="description" placeholder="Description" name="description"/>
        @error('description')
            <x-admin::form.error :message="$message"/>
        @else
            <x-admin::form.description description="Short description of the Price as displayed to users"/>
        @enderror
    </div>
    <div class="mb-3">
        <x-admin::form.label for="period_in_days" label="Recurring Type"/>
        <x-admin::form.select wire:model.change="period_in_days" id="period_in_days" name="period_in_days"
                              :options="$recurringTypes"/>
        @error('period_in_days')
            <x-admin::form.error :message="$message"/>
        @else
            <x-admin::form.description description="How often this package renews"/>
        @enderror
    </div>

    @if($period_in_days > 0)
        <div class="mb-3">
            <x-admin::form.label for="price" label="Renewal Price"/>
            <x-admin::form.input type="number" wire:model="price" id="price" name="price"
                                 placeholder="Renewal Price"/>
            @error('price')
            <x-admin::form.error :message="$message"/>
            @else
                <x-admin::form.description
                    description="The renewal price that is paid {{ $recurringTypes[$period_in_days] ?? 'Every x period' }}"/>
                @enderror
        </div>
    @else
        <div class="mb-3">
            <x-admin::form.label for="price" label="Price"/>
            <x-admin::form.input type="number" wire:model.change="price" id="price" name="price"
                                 placeholder="Price"/>
            @error('price')
            <x-admin::form.error :message="$message"/>
            @else
                <x-admin::form.description description="The base price of this package"/>
                @enderror
        </div>
    @endif

    <div class="mb-3">
        <x-admin::form.label for="setup_fee" label="Setup Fee"/>
        <x-admin::form.input type="number" wire:model="setup_fee" id="setup_fee" name="setup_fee"
                             placeholder="Setup Price"/>
        @error('setup_fee')
        <x-admin::form.error :message="$message"/>
        @else
            <x-admin::form.description description="The setup fee for this package"/>
            @enderror
    </div>

    <div class="mb-3">
        <x-admin::form.label for="upgrade_fee" label="Upgrade Fee"/>
        <x-admin::form.input type="number" wire:model="upgrade_fee" id="upgrade_fee" name="upgrade_fee"
                             placeholder="Upgrade Fee"/>
        @error('upgrade_fee')
        <x-admin::form.error :message="$message"/>
        @else
            <x-admin::form.description description="The upgrade fee for this package"/>
            @enderror
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
    </div>
</form>
