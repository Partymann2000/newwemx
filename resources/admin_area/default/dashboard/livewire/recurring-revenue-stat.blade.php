<?php

use Livewire\Volt\Component;
use App\Models\Subscription;

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
    public function recurringRevenueData()
    {
        $baseCurrency = baseCurrency();
        $range = $this->activeDateRange;
        $now = now();

        $currentQuery = Subscription::query();
        $previousQuery = Subscription::query();

        if ($range !== 9999) {
            $currentStart = $now->copy()->subDays($range);
            $previousStart = $now->copy()->subDays($range * 2);
            $previousEnd = $currentStart->copy();

            $currentQuery->where('created_at', '>=', $currentStart);
            $previousQuery->whereBetween('created_at', [$previousStart, $previousEnd]);
        }

        $currentSubscriptions = $currentQuery->get();
        $previousSubscriptions = $previousQuery->get();

        $currentActiveSubscriptions = $currentSubscriptions->filter(fn ($subscription) => $subscription->isActive());
        $previousActiveSubscriptions = $previousSubscriptions->filter(fn ($subscription) => $subscription->isActive());

        $currentAmount = $currentActiveSubscriptions->sum(function ($subscription) use ($baseCurrency) {
            $convertedAmount = price($subscription->amount, to: $baseCurrency, in: $subscription->currency, absolute: true);
            $frequencyInDays = max((int) $subscription->frequency, 1);

            // Normalize every active subscription to monthly recurring revenue.
            return ($convertedAmount / $frequencyInDays) * 30;
        });

        $previousAmount = $previousActiveSubscriptions->sum(function ($subscription) use ($baseCurrency) {
            $convertedAmount = price($subscription->amount, to: $baseCurrency, in: $subscription->currency, absolute: true);
            $frequencyInDays = max((int) $subscription->frequency, 1);

            return ($convertedAmount / $frequencyInDays) * 30;
        });

        if ($previousAmount > 0) {
            $change = round((($currentAmount - $previousAmount) / $previousAmount) * 100, 2);
        } elseif ($currentAmount > 0) {
            $change = 100;
        } else {
            $change = 0;
        }

        return [
            'amount' => $currentAmount,
            'active_count' => $currentActiveSubscriptions->count(),
            'inactive_count' => $currentSubscriptions->where('status', 'inactive')->count(),
            'cancelled_count' => $currentSubscriptions->where('status', 'cancelled')->count(),
            'change_compared_to_previous_period' => $change,
        ];
    }
}

?>

<div class="col-sm-6 col-lg-3">
    <x-admin::dashboard.metric-stat-card
        title="Recurring Revenue"
        :value="PriceIn($this->recurringRevenueData['amount'], baseCurrency()) . ' / month'"
        :description="'MRR from ' . $this->recurringRevenueData['active_count'] . ' active subscriptions (' . $this->recurringRevenueData['inactive_count'] . ' inactive, ' . $this->recurringRevenueData['cancelled_count'] . ' cancelled)'"
        :change="$this->recurringRevenueData['change_compared_to_previous_period'] ?? 0"
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
