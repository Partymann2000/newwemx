<?php

namespace App\Handlers\Cart;
use App\Models\CartOrderItem;
use App\Models\Order;

class PackagePurchasedHandler
{
    public function handle(CartOrderItem $item)
    {
        $price = $item->cartable;

        $orderData = [
            'user_id' => $item->user_id,
            'package_id' => $price->package_id,
            'package_price_id' => $price->id,
            'status' => 'pending',
            'cycle_price' => $price->getDailyPrice(),
            'setup_fee' => $price->setup_fee,
            'upgrade_fee' => $price->upgrade_fee,
            'period_in_days' => $price->period_in_days,
            'last_renewed_at' => now(),
        ];

        if($price->isRecurring()) {
            $orderData['due_date'] = now()->addDays($price->period_in_days);
        }

        $order = Order::create($orderData);

        foreach($item->options as $option) {
            $order->prices()->create([
                'description' => $option['name'],
                'type' => 'config_option',
                'key' => $option['key'],
                'value' => $option['value'],
                'cycle_price' => ($price->period_in_days > 0) ? $option['price'] / $price->period_in_days : $option['price'],
                'setup_fee' => 0,
                'upgrade_fee' => 0,
            ]);
        }

        // if package global_quantity is not -1, decrease the global quantity by 1
        if ($price->package->global_quantity !== -1) {
            $price->package->decrement('global_quantity', 1);
        }

        $order->createServer(
            dispatch: true,
        );
    }
}
