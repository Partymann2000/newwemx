<?php

use App\Models\Package;
use Livewire\Volt\Component;
use Illuminate\Support\Arr;

new class extends Component
{
    public $package;

    public array $config = [];

    public function mount(Package $package): void
    {
        $this->package = $package;

        $this->config = $package->data ?? [];

        $this->setDefaultData();
    }

    public function updatePackageConfig(): void
    {
        Package::actions()->storePackageDataPackageAsAdmin([
            'package_id' => $this->package->id,
            'data' => $this->config,
        ]);

        // reload the package to reflect changes
        $this->package->refresh();

        // set default data again to ensure all keys are present
        $this->setDefaultData();
    }

    public function setDefaultData(): void
    {
        $defaultData = $this->package->getPackageConfig()->pluck('default_value', 'key')->toArray();

        $defaultData = Arr::undot($defaultData);

        $this->config = array_merge($defaultData, $this->config);
    }

    public function resetToDefault(): void
    {
        $this->config = [];

        $this->package->update([
            'data' => null,
        ]);
    }
}
?>

<form class="row" wire:submit="updatePackageConfig()">
    @foreach($package->getPackageConfig()->toArray() as $field)
        <div class="mb-3 {{ $field['col'] ?? 'col-4' }}">
            <x-admin::form.label for="{{ $field['key'] }}" label="{{ $field['name'] }}"/>

            @if($field['type'] === 'select')
                <x-admin::form.select wire:model="config.{{ $field['key'] }}"  id="{{ $field['key'] }}" name="{{ $field['key'] }}" :options="$field['options'] ?? []"/>
            @elseif($field['type'] === 'textarea')
                <x-admin::form.textarea wire:model="config.{{ $field['key'] }}" id="{{ $field['key'] }}" name="{{ $field['key'] }}" placeholder="{{ $field['name'] }}"/>
            @else
                <x-admin::form.input type="{{ $field['type'] }}" wire:model="config.{{ $field['key'] }}" id="{{ $field['key'] }}" name="{{ $field['key'] }}" placeholder="{{ $field['name'] }}"/>
            @endif

            @error($field['key'])
                <x-admin::form.error :message="$message" />
            @else
                <x-admin::form.description description="{{ $field['description'] }}"/>
            @enderror
        </div>
    @endforeach
    <div class="d-flex justify-content-between">
        <x-admin::button color="danger" class="me-2" type="button" label="Reset To Default" wire:click="resetToDefault" wire:confirm.prompt="Type 'confirm' to reset to default|confirm"  />
        <x-admin::button type="submit" label="Update"/>
    </div>
</form>
