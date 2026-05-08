<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component {
    public $package;

    public $key = 'custom';

    public $customKey = '';

    public $label = 'Label';

    public $description = 'Description';

    public $rules = 'required';

    public $type = 'text';

    public $onetime_day_equivalent = 365; // default to 1 year

    public $keys = [];

    public $keyOptions = [];

    public function mount($package)
    {
        $this->keys = $package->getPackageConfigOptions();
        $this->keyOptions = collect($this->keys)->pluck('name', 'key')->toArray();

        $this->keyOptions = array_merge($this->keyOptions, ['custom' => 'Custom']);
    }

    public function updated()
    {
        // when a key is selected, set the label and description
        if ($this->key) {
            // attempt to find the key in the package config options, its not indexed
            $option = collect($this->keys)->firstWhere('key', $this->key);
            if ($option) {
                $this->label = $option['name'] ?? '';
                $this->description = $option['description'] ?? '';
                $this->rules = $option['rules'] ?? '';
            }
        }
    }

    public function createConfigOption()
    {
        // validate the input
        Package::actions()->createConfigOptionAsAdmin([
            'package_id' => $this->package->id,
            'key' => $this->key === 'custom' ? $this->customKey : $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'type' => $this->type,
            'onetime_day_equivalent' => $this->onetime_day_equivalent,
            'default_value' => $this->package->data($this->key, ''),
        ]);

        $this->redirect(route('admin.packages.edit', [
            'package' => $this->package->id,
            'packageEditPage' => 'config_options',
        ]), navigate: true);
    }
}

?>

<div class="modal modal-blur fade" id="create-config-option" tabindex="-1" role="dialog" aria-hidden="true"
     wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Config Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="mb-3">
                        <x-admin::form.label for="field" label="Field"/>
                        <x-admin::form.select id="field" wire:model.change="key" :options="$keyOptions"
                                              placeholder="Select a field"/>
                        @error('key')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description
                                description="The field key is used to identify the config option in the package's configuration."/>
                        @enderror
                    </div>
                    @if($this->key === 'custom')
                        <div class="mb-3">
                            <x-admin::form.label for="key" label="Custom Key"/>
                            <x-admin::form.input required id="key" type="text" wire:model="customKey" placeholder="Custom Key"/>
                            @error('key')
                                <x-admin::form.error :message="$message"/>
                            @else
                                <x-admin::form.description
                                    description="Enter a custom key (e.g server_name)"/>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <x-admin::form.label for="type" label="Type"/>
                        <x-admin::form.select id="type" wire:model="type" :options="[
                            'text' => 'Text',
                            'textarea' => 'Textarea',
                            'select' => 'Select',
                            'radio' => 'Radio',
                            'range' => 'Range',
                            'number' => 'Number',
                            'email' => 'Email',
                            'password' => 'Password',
                        ]"/>
                        @error('type')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description
                                description="The type of the field. This will determine how the field is rendered in the package's configuration."/>
                        @enderror
                    </div>

                    @if(in_array($type, ['select', 'radio', 'range', 'number']))
                        <div class="mb-3">
                            <x-admin::form.label for="onetime_day_equivalent" label="Onetime Day Equivalent"/>
                            <x-admin::form.input id="onetime_day_equivalent" type="number" wire:model="onetime_day_equivalent" placeholder="Onetime Day Equivalent"/>
                            @error('onetime_day_equivalent')
                                <x-admin::form.error :message="$message"/>
                            @else
                                <x-admin::form.description>
                                    The equivalent of days for onetime packages, default is 365 or 1 year. Set to 0 to disable.
                                </x-admin::form.description>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" wire:click="createConfigOption">Create Option</button>
            </div>
        </div>
    </div>
</div>
