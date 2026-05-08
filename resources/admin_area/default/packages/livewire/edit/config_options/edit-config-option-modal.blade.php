<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component {
    public $configOption;

    public $key = '';

    public $label = '';

    public $description = '';

    public $default_value = '';

    public $rules = '';

    public $onetime_day_equivalent = 365; // default to 1 year

    public $type = 'text';

    public array $data = [
        'options' => [
            [
                'value' => '',
                'name' => '',
                'description' => '',
                'icon_url' => '',
                'daily_price' => 0
            ],
        ],
        'min_value' => 1,
        'max_value' => 6,
        'step_value' => 1,
        'daily_price' => 0,
        'free_value' => 0,
    ];

    public function mount($configOption)
    {
        $this->configOption = $configOption;

        // set the initial values from the option
        $this->key = $configOption->key ?? '';
        $this->label = $configOption->label ?? '';
        $this->description = $configOption->description ?? '';
        $this->rules = $configOption->rules ?? '';
        $this->onetime_day_equivalent = $configOption->onetime_day_equivalent ?? $this->onetime_day_equivalent;
        $this->type = $configOption->type ?? 'text';
        $this->data = $configOption->data ?? $this->data;
        $this->default_value = $configOption->default_value ?? '';
    }

    public function addOption()
    {
        // duplicate the last option and add it to the options array
        $lastOption = end($this->data['options']);
        $this->data['options'][] = [
            'value' => $lastOption['value'] ?? '',
            'name' => $lastOption['name'] ?? '',
            'description' => $lastOption['description'] ?? '',
            'icon_url' => $lastOption['icon_url'] ?? '',
            'daily_price' => $lastOption['daily_price'] ?? 0,
        ];
    }

    public function removeOption()
    {
        // remove the last option from the options array
        if (count($this->data['options']) > 1) {
            array_pop($this->data['options']);
        }
    }

    public function saveConfigOption()
    {
        Package::actions()->updateConfigOptionAsAdmin([
            'config_option_id' => $this->configOption->id,
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'default_value' => $this->default_value,
            'rules' => $this->rules,
            'onetime_day_equivalent' => $this->onetime_day_equivalent,
            'type' => $this->type,
            'data' => $this->data,
        ]);

        $this->redirect(route('admin.packages.edit', [
            'package' => $this->configOption->package_id,
            'packageEditPage' => 'config_options',
        ]), navigate: true);
    }

    public function deleteOption()
    {
        Package::actions()->deleteConfigOptionAsAdmin([
            'config_option_id' => $this->configOption->id
        ]);

        $this->redirect(route('admin.packages.edit', [
            'package' => $this->configOption->package_id,
            'packageEditPage' => 'config_options',
        ]), navigate: true);
    }
}

?>

<div class="modal modal-blur fade" id="edit-config-option-{{ $configOption->id }}" tabindex="-1" role="dialog"
     aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Config Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="mb-3">
                        <x-admin::form.label for="field-{{ $configOption->id }}" label="Field"/>
                        <x-admin::form.input id="field-{{ $configOption->id }}" type="text" disabled placeholder="{{ $configOption->key }}"/>
                        @error('key')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description
                                description="The field key is used to identify the config option in the package's configuration."/>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <x-admin::form.label for="label-{{ $configOption->id }}" label="Label"/>
                        <x-admin::form.input id="label-{{ $configOption->id }}" type="text" wire:model="label"
                                             placeholder="Label"/>
                        @error('label')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description description="The display name of the field"/>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <x-admin::form.label for="description-{{ $configOption->id }}" label="Description"/>
                        <x-admin::form.input id="description-{{ $configOption->id }}" type="text"
                                             wire:model="description" placeholder="Description"/>
                        @error('description')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description description="The display description of the field"/>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <x-admin::form.label for="default_value-{{ $configOption->id }}" label="Default Value"/>
                        <x-admin::form.input id="default_value-{{ $configOption->id }}" type="text"
                                             wire:model="default_value" placeholder="Default Value"/>
                        @error('default_value')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description description="The default value of the field. This will be used if the user does not provide a value."/>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <x-admin::form.label for="rules-{{ $configOption->id }}" label="Rules"/>
                        <x-admin::form.input id="rules-{{ $configOption->id }}" type="text" wire:model="rules" placeholder="Rules"/>
                        @error('rules')
                            <x-admin::form.error :message="$message"/>
                        @else
                            <x-admin::form.description><a href="https://laravel.com/docs/12.x/validation" target="_blank">Laravel Validation Rules</a></x-admin::form.description>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <x-admin::form.label for="type-{{ $configOption->id }}" label="Type"/>
                        <x-admin::form.select id="type-{{ $configOption->id }}" wire:model.change="type" :options="[
                            'text' => 'Text',
                            'textarea' => 'Textarea',
                            'select' => 'Select',
                            'radio' => 'Radio',
                            'range' => 'Range',
                            'number' => 'Number',
                            'email' => 'Email',
                            'password' => 'Password',
                        ]"/>
                        <x-admin::form.description
                            description="The type of the field. This will determine how the field is rendered in the package's configuration."/>
                    </div>

                    @if(in_array($type, ['select', 'radio', 'range', 'number']))
                        <div class="mb-3">
                            <x-admin::form.label for="onetime_day_equivalent-{{ $configOption->id }}" label="Onetime Day Equivalent"/>
                            <x-admin::form.input id="onetime_day_equivalent-{{ $configOption->id }}" type="number" wire:model="onetime_day_equivalent" placeholder="Onetime Day Equivalent"/>
                            @error('rules')
                            <x-admin::form.error :message="$message"/>
                            @else
                                <x-admin::form.description>
                                    The equivalent of days for onetime packages, default is 365 or 1 year. Set to 0 to disable.
                                </x-admin::form.description>
                                @enderror
                        </div>
                    @endif

                    @if(in_array($type, ['select', 'radio']))
                        @foreach($data['options'] as $index => $option)
                            <div class="row mb-3">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <x-admin::form.label for="option_value_{{ $index }}-{{ $configOption->id }}"
                                                         label="Option Value"/>
                                    <x-admin::form.input id="option_value_{{ $index }}-{{ $configOption->id }}"
                                                         type="text" wire:model="data.options.{{ $index }}.value"
                                                         placeholder="Option Value"/>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <x-admin::form.label for="option_name_{{ $index }}-{{ $configOption->id }}"
                                                         :label="$type === 'radio' ? 'Title' : 'Option Name'"/>
                                    <x-admin::form.input id="option_name_{{ $index }}-{{ $configOption->id }}"
                                                         type="text" wire:model="data.options.{{ $index }}.name"
                                                         :placeholder="$type === 'radio' ? 'Title' : 'Option Name'"/>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <x-admin::form.label for="option_price_{{ $index }}-{{ $configOption->id }}"
                                                         label="Daily Price"/>
                                    <x-admin::form.input id="option_price_{{ $index }}-{{ $configOption->id }}"
                                                         type="number" wire:model="data.options.{{ $index }}.daily_price"
                                                         placeholder="Daily Price"/>
                                </div>
                                @if($type === 'radio')
                                    <div class="col-md-3">
                                        <x-admin::form.label for="option_icon_{{ $index }}-{{ $configOption->id }}"
                                                             label="Icon URL (Optional)"/>
                                        <x-admin::form.input id="option_icon_{{ $index }}-{{ $configOption->id }}"
                                                             type="url" wire:model="data.options.{{ $index }}.icon_url"
                                                             placeholder="https://..."/>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <x-admin::form.label for="option_description_{{ $index }}-{{ $configOption->id }}"
                                                             label="Description (Optional)"/>
                                        <x-admin::form.input id="option_description_{{ $index }}-{{ $configOption->id }}"
                                                             type="text" wire:model="data.options.{{ $index }}.description"
                                                             placeholder="Short description"/>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        <div class="col-12 mt-3 text-end d-flex flex-column">
                            <a class="text-success ms-2" wire:click="addOption" style="cursor: pointer;">Add Option</a>
                            <a class="text-danger" wire:click="removeOption" style="cursor: pointer;">Remove Option</a>
                        </div>
                    @endif

                    @if($type === 'range' || $type === 'number')
                        <div class="col-12 mb-3">
                            <x-admin::form.label for="free_value-{{ $configOption->id }}" label="Free Value"/>
                            <x-admin::form.input id="free_value-{{ $configOption->id }}" type="number"
                                                 wire:model="data.free_value" placeholder="Free Value"/>
                            <x-admin::form.description description="The value that will be free of charge. MUST be a numeric value between the MINIMUM and MAXIMUM"/>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <x-admin::form.label for="min_value-{{ $configOption->id }}" label="Minimum Value"/>
                                <x-admin::form.input id="min_value-{{ $configOption->id }}" type="number"
                                                     wire:model="data.min_value" placeholder="Minimum Value"/>
                            </div>
                            <div class="col-6 mb-3">
                                <x-admin::form.label for="max_value-{{ $configOption->id }}" label="Maximum Value"/>
                                <x-admin::form.input id="max_value-{{ $configOption->id }}" type="number"
                                                     wire:model="data.max_value" placeholder="Maximum Value"/>
                            </div>
                            <div class="col-6">
                                <x-admin::form.label for="step_value-{{ $configOption->id }}" label="Step Value"/>
                                <x-admin::form.input id="step_value-{{ $configOption->id }}" type="number"
                                                     wire:model="data.step_value" placeholder="Step Value"/>
                            </div>
                            <div class="col-6">
                                <x-admin::form.label for="daily_price-{{ $configOption->id }}"
                                                     label="Daily Price Per Unit"/>
                                <x-admin::form.input id="daily_price-{{ $configOption->id }}" type="number"
                                                     wire:model="data.daily_price" placeholder="Daily Price Per Step Value"/>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" wire:click="deleteOption" wire:confirm="">Delete</button>
                <button type="button" class="btn btn-primary" wire:click="saveConfigOption">Update</button>
            </div>
        </div>
    </div>
</div>
