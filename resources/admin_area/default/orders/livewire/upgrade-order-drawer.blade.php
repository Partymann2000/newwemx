<?php

use Livewire\Volt\Component;
use App\Models\PackagePrice;

new class extends Component
{
    public $order;

    public $packagePriceId;

    public $availablePackagePrices = [];

    public bool $supportsUpgradeOrDowngrade = false;

    public function mount()
    {
        $functions = $this->order->package->serverConnection->server->functions();
        $this->supportsUpgradeOrDowngrade = method_exists($functions, 'upgradeOrDowngrade');

        $connectionId = $this->order->package->connection_id;
        $currentPackagePriceId = $this->order->package_price_id;

        $this->availablePackagePrices = PackagePrice::query()
            ->where('id', '!=', $currentPackagePriceId)
            ->whereHas('package', function ($query) use ($connectionId) {
                $query->where('connection_id', $connectionId);
            })
            ->with('package')
            ->get()
            ->mapWithKeys(function ($price) {
                return [
                    $price->id => $price->package->name . ' - ' . price($price->price) . ' / ' . $price->cycle(),
                ];
            })
            ->toArray();
    }

    public function upgradeOrder()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.update'), 403);

        if (!$this->supportsUpgradeOrDowngrade) {
            $this->addError('packagePriceId', 'This server connection does not support upgrade/downgrade.');
            return;
        }

        $this->order->actions()->upgradeOrderAsAdmin([
            'order_id' => $this->order->id,
            'package_price_id' => $this->packagePriceId,
        ]);

        $this->dispatch('order-updated');
    }
}

?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="upgradeOrderDrawer" aria-labelledby="upgradeOrderDrawerLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="upgradeOrderDrawerLabel">Upgrade/Downgrade Order</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @if(!$supportsUpgradeOrDowngrade)
            <x-admin::form.error message="This order's server connection does not expose an upgradeOrDowngrade() function." />
        @elseif(empty($availablePackagePrices))
            <x-admin::form.error message="No compatible package prices found on this server connection." />
        @else
            <div class="row">
                <div class="form-group col-md-12 col-12 mb-3">
                    <x-admin::form.label>Target Package Price</x-admin::form.label>
                    <x-admin::form.select wire:model="packagePriceId" :options="$availablePackagePrices" searchable />
                    <x-admin::form.description>Select the target package/price for this upgrade or downgrade.</x-admin::form.description>
                    @error('packagePriceId')
                        <x-admin::form.error :message="$message" />
                    @enderror
                    @error('package_price_id')
                        <x-admin::form.error :message="$message" />
                    @enderror
                </div>
            </div>
            <div class="mt-3 text-end">
                <button class="btn btn-primary" wire:click="upgradeOrder" type="button" data-bs-dismiss="offcanvas">
                    Apply Change
                </button>
            </div>
        @endif
    </div>
</div>
