<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $enable_registrations;

    public $require_address;

    public $lastModifiedTimestamps;

    public function mount()
    {
        $this->enable_registrations = (bool) settings('enable_registrations', true);
        $this->require_address = settings('require_address', false) == '1' ? true : false;
        $this->lastModifiedTimestamps = \App\Models\Setting::whereIn('key', ['enable_registrations', 'require_address'])->pluck('updated_at', 'key');
    }

    public function saveChanges()
    {
        \App\Models\Setting::actions()->updateApplicationAuthenticationSettingsAsAdmin([
            'enable_registrations' => $this->enable_registrations,
            'require_address' => $this->require_address,
        ]);

        $this->dispatch('alert', 'success', 'Settings saved successfully.');
    }

    public function resetToDefault()
    {
        $this->enable_registrations = true;
        $this->require_address = false;

        $this->dispatch('alert', 'success', 'Settings reset to default.');
    }
}

?>

<div>
    <x-admin::settings.page-form title="Authentication">

            <div class="mb-4">
                <h3 class="card-title">Enable Registrations</h3>
                <p class="card-subtitle">
                    Do you want to enable user registrations on your application?
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="enable_registrations" value="1">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                        <small class="form-hint">
                            Last modified {{ isset($lastModifiedTimestamps['enable_registrations']) ? $lastModifiedTimestamps['enable_registrations']->diffForHumans() : 'Never' }}
                        </small>
                    </div>
                    @error('enable_registrations')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Require Address</h3>
                <p class="card-subtitle">
                    Do you want to require users to provide their address if its not provided or incomplete?
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="require_address" value="1">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                        <small class="form-hint">
                            Last modified {{ isset($lastModifiedTimestamps['require_address']) ? $lastModifiedTimestamps['require_address']->diffForHumans() : 'Never' }}
                        </small>
                    </div>
                    @error('require_address')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

        <x-slot:footerActions>
                <button type="button" class="btn btn-1 btn-danger" wire:click="resetToDefault" wire:confirm="Are you sure you want to reset metrics to default?">
                    Reset to Default
                </button>
        </x-slot:footerActions>
    </x-admin::settings.page-form>
</div>
