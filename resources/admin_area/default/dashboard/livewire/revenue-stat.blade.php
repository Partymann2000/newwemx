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
    public function earningsData()
    {
        return Statistics::revenueFromPaymentsLastDays($this->activeDateRange, baseCurrency());
    }
}

?>

<div class="col-sm-6 col-lg-3">
    <x-admin::dashboard.metric-stat-card
        title="Earnings"
        :value="PriceIn($this->earningsData['amount'], baseCurrency())"
        :description="'From ' . $this->earningsData['payment_count'] . ' unique payments in ' . $this->earningsData['currency_count'] . ' different currencies'"
        :change="$this->earningsData['change_compared_to_previous_period'] ?? 0"
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
