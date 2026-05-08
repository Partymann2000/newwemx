<?php

namespace App\Jobs\Orders;

use App\Events\Orders\Errors\OrderUnsuspensionFailed;
use App\Events\Orders\OrderUnsuspended;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Order;
use Throwable;

class OrderUnsuspendServer implements ShouldQueue
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
        // Attempt to unsuspend the server for the order
        $connection = $this->order->package->serverConnection;
        $this->order->serverMethods()->unsuspend($this->order, $connection);

        // Mark the order as active
        $this->order->update([
            'status' => 'active',
        ]);

        // notify the user about the unsuspension
        $this->order->emailOrderUnsuspension();

        // Dispatch an event that the order has been unsuspended
        OrderUnsuspended::dispatch($this->order);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->order->exceptions()->create([
            'action' => 'unsuspend',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        // set the order status to failed
        $this->order->update([
            'status' => 'failed',
        ]);

        // Dispatch an event that the order unsuspension has failed
        OrderUnsuspensionFailed::dispatch($this->order, $exception);
    }
}
