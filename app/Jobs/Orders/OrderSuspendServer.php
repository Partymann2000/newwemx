<?php

namespace App\Jobs\Orders;

use App\Events\Orders\Errors\OrderSuspensionFailed;
use App\Events\Orders\OrderSuspended;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Order;
use Throwable;

class OrderSuspendServer implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Here we will attempt to suspend the server for the order
        $connection = $this->order->package->serverConnection;
        $this->order->serverMethods()->suspend($this->order, $connection);

        // Mark the order as suspended
        $this->order->update([
            'status' => 'suspended',
        ]);

        // notify the user about the suspension
        $this->order->emailOrderSuspension();

        // Dispatch an event that the order has been suspended
        OrderSuspended::dispatch($this->order);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->order->exceptions()->create([
            'action' => 'suspend',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        // set the order status to failed
        $this->order->update([
            'status' => 'failed',
        ]);

        // Dispatch an event that the order suspension has failed
        OrderSuspensionFailed::dispatch($this->order, $exception);
    }
}
