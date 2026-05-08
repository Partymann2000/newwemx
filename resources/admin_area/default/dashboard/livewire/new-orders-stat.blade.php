<?php

use Livewire\Volt\Component;
use App\Helpers\Statistics;

new class extends Component
{
    #[\Livewire\Attributes\Session]
    public int $activeDateRange = 30;

    public array $dateRanges = [
        1 => 'Today',
        7 => 'Last 7 Days',
        14 => 'Last 14 Days',
        30 => 'Last 30 Days',
        90 => 'Last 90 Days',
        180 => 'Last 180 Days',
        365 => 'Last 365 Days',
        9999 => 'All Time',
    ];

    public function setActiveDateRange($range)
    {
        if (array_key_exists($range, $this->dateRanges)) {
            $this->activeDateRange = $range;
        }
    }

    #[\Livewire\Attributes\Computed]
    public function ordersData()
    {
        return Statistics::newOrdersLastDays($this->activeDateRange);
    }
}

?>

<div class="col-sm-6 col-lg-3">
    <x-admin::dashboard.metric-stat-card
        title="New Orders"
        :value="$this->ordersData['count'] . ' orders'"
        :description="$this->ordersData['count'] . ' new orders, compared to ' . $this->ordersData['previous_count'] . ' in the previous period.'"
        :change="$this->ordersData['change_compared_to_previous_period'] ?? 0"
    >
        <x-slot:actions>
            <div class="dropdown">
                <a class="dropdown-toggle text-secondary" href="#" data-bs-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">{{ $dateRanges[$activeDateRange] }}</a>
                <div class="dropdown-menu dropdown-menu-end">
                    @foreach($dateRanges as $range => $label)
                        <button class="dropdown-item" wire:click="setActiveDateRange({{ $range }})">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </x-slot:actions>
    </x-admin::dashboard.metric-stat-card>
</div>
