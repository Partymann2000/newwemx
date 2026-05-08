<?php

use Livewire\Volt\Component;
use Illuminate\View\View;
use App\Models\Setting;

new class extends Component
{
    public $date_format;

    public $renewal_reminder_period;

    public $suspend_period;

    public $terminate_period;

    public $enable_delete_terminated_period;

    public $delete_terminated_period;

    public function mount()
    {
        $this->date_format = settings('date_format', 'd M Y H:i');
        $this->renewal_reminder_period = settings('renewal_reminder_period', 3);
        $this->suspend_period = settings('suspend_period', 3);
        $this->terminate_period = settings('terminate_period', 7);
        $this->enable_delete_terminated_period = settings('enable_delete_terminated_period', false) == '1' ? true : false;
        $this->delete_terminated_period = settings('delete_terminated_period', 0);
    }

    public function saveChanges()
    {
        Setting::actions()->updateApplicationMetricsAsAdmin([
            'date_format' => $this->date_format,
            'renewal_reminder_period' => $this->renewal_reminder_period,
            'suspend_period' => $this->suspend_period,
            'terminate_period' => $this->terminate_period,
            'enable_delete_terminated_period' => $this->enable_delete_terminated_period,
            'delete_terminated_period' => $this->delete_terminated_period,
        ]);

        $this->dispatch('alert', 'success', 'Settings saved successfully.');
    }

    public function resetToDefault()
    {
        $this->date_format = 'd M Y';
        $this->renewal_reminder_period = 3;
        $this->suspend_period = 3;
        $this->terminate_period = 7;
        $this->enable_delete_terminated_period = false;
        $this->delete_terminated_period = 30;

        $this->dispatch('alert', 'success', 'Settings reset to default.');
    }
}

?>

<div>
    <x-admin::settings.page-form title="Metrics">

            <div class="mb-4">
                <h3 class="card-title">
                    Date Format (Current : {{ now()->format($date_format) }})
                </h3>
                <p class="card-subtitle">
                    The format in which dates are displayed to users. You can use the following format characters: d - Day of the month (01 to 31), M - Short month name (Jan to Dec), Y - Four-digit year (e.g., 2023), H - Hour in 24-hour format (00 to 23), i - Minutes (00 to 59).
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <x-admin::form.input wire:model="date_format" type="text" />
                    </div>
                    @error('date_format')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Renewal Reminder</h3>
                <p class="card-subtitle">
                    How many days before the orders due date should a renewal reminder be sent to the user? For example, if you set this to 3, a reminder will be sent to the user 3 days before the order is due via email.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <x-admin::form.input wire:model="renewal_reminder_period" type="number" />
                    </div>
                    @error('renewal_reminder_period')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Suspension Period</h3>
                <p class="card-subtitle">
                    After how many days should expired orders be suspended? For example, if you set this to 3, orders that are 3 days past their due date will be suspended.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <x-admin::form.input wire:model="suspend_period" type="number" />
                    </div>
                    @error('suspend_period')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Termination Period</h3>
                <p class="card-subtitle">
                    After how many days should expired orders be terminated? For example, if you set this to 7, orders that are 7 days past their due date will be terminated.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <x-admin::form.input wire:model="terminate_period" type="number" />
                    </div>
                    @error('terminate_period')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Delete Terminated Orders Period</h3>
                <p class="card-subtitle">
                    After how many days should terminated orders be deleted?
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model.live="enable_delete_terminated_period" value="1">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                        @if($enable_delete_terminated_period == true)
                            <x-admin::form.input wire:model="delete_terminated_period" type="number" />
                        @endif
                    </div>
                    @error('delete_terminated_period')
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
