<?php

use Livewire\Volt\Component;
use Illuminate\View\View;
use App\Models\Setting;
use App\Models\Extension;

new class extends Component
{
    public $extension;

    public $values = [];

    public $fields = [];

    public $lastModifiedKeys = [];

    public function mount()
    {
        $this->extension = Extension::where('status', 'enabled')->where('identifier', request()->get('extension'))->firstOrFail();
        $this->fields = $this->extension->extension()->getFieldsForSettingsPage()['settings'] ?? [];

        $this->values = collect($this->fields)->mapWithKeys(function ($field, $key) {
            return [$key => settings($key)];
        })->toArray();

        $keys = array_keys($this->fields);

        $this->lastModifiedKeys = Setting::whereIn('key', $keys)->pluck('updated_at', 'key')->toArray();
    }

    public function saveChanges()
    {
        // map the fields as rules
        $rules = collect($this->fields)->mapWithKeys(function ($field, $key) {
            return ['values.'. $key => $field['rules'] ?? 'required'];
        })->toArray();

        // validate the fields
        $values = $this->validate($rules);

        // store the data
        Setting::store($values['values']);

        $this->dispatch('alert', 'success', 'Settings saved successfully.');
    }
}

?>

<div>
    <x-admin::settings.page-form :title="$extension->extension()->getSettingsTitle()">

            @foreach($fields as $key => $field)
            <div class="mb-4">
                <h3 class="card-title">{{ $field['label'] }}</h3>
                <p class="card-subtitle">
                    {{ $field['description'] ?? '' }}
                </p>
                <div class="row g-2">
                    <div class="col">
                        @if($field['type'] === 'select')
                            <x-admin::form.select wire:model="values.{{ $key }}" name="{{ $key }}" value="{{ settings($key) }}" :options="$field['options']" searchable />
                        @else
                            <x-admin::form.input wire:model="values.{{ $key }}" name="{{ $key }}" placeholder="{{ $field['label'] }}" />
                        @endif
                    </div>
                    @error('values.'. $key)
                        <x-admin::form.error :message="$message" />
                    @else
                        <small class="form-hint">
                            Last modified {{ isset($lastModifiedKeys[$key]) ? $lastModifiedKeys[$key]->diffForHumans() : 'never' }}
                        </small>
                    @enderror
                </div>
            </div>
            @endforeach

    </x-admin::settings.page-form>
</div>
