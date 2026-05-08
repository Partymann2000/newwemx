<?php

namespace App\Console\Commands\Cronjobs;

use App\Models\AppTaskLog;
use App\Models\Order;
use Illuminate\Console\Command;

class RenewOrdersWithBalanceRenewal extends Command
{
    protected $signature = 'cronjobs:orders:renew-balance-renewals';

    protected $description = 'Renews active orders with auto-balance renewal enabled 3 days before their due date';

    public function handle(): void
    {
        $daysBeforeDue = 3;
        $orders = Order::getOrdersPastDueDate($daysBeforeDue)
                        ->where('auto_balance_renew', true)
                        ->where('status', 'active')
                        ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders to auto renew.');
            return;
        } else {
            $this->info("Found {$orders->count()} orders to auto renew.");
        }

        $successRenewedOrders = [];
        foreach ($orders as $order) {
            if (!$order->hasEnoughBalanceToRenew()) {
                $this->error("Order #{$order->id} does not have enough balance to renew.");
                $order->emailNotEnoughBalanceForAutoRenewal();
                continue;
            }

            try {
                $order->attemptBalanceRenewal();

                $order->log([
                    'user_id' => null,
                    'action' => 'order_renewed',
                    'description' => 'Order has been renewed by system scheduler using balance renewal.',
                ]);

                $successRenewedOrders[] = $order->id;
            } catch (\Exception $e) {
                $this->error("Failed to renew Order #{$order->id}: {$e->getMessage()}");
                continue;
            }

            $this->info("Order #{$order->id} has been renewed.");
        }

        // log renewal event to application
        if (!empty($successRenewedOrders)) {
            AppTaskLog::create([
                'task' => 'renew_orders_with_balance_renewal',
                'status' => 'completed',
                'message' => "{$orders->count()} orders were renewed using balance renewal.",
                'show' => true,
                'data' => ['orders' => $successRenewedOrders],
            ]);
        }

        $this->info('Order renewal process completed.');
    }
}
