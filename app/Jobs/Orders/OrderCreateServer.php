<?php

namespace App\Jobs\Orders;

use App\Events\Orders\Errors\OrderActivationFailed;
use App\Events\Orders\OrderActivated;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class OrderCreateServer implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // if order status is not pending, return
        if ($this->order->status !== 'pending') {
            return;
        }

        // set the order status to processing
        $this->order->update([
            'status' => 'processing',
        ]);

        // here we will attempt to create a new server for the order
        $connection = $this->order->package->serverConnection;
        $this->order->serverMethods()->create($this->order, $connection);

        // mark the order as active
        $this->order->update([
            'status' => 'active',
        ]);

        // if the order server creation took longer than 5 minutes, since the order creation, email the user
        if ($this->order->created_at->diffInMinutes(now()) > 5) {
            $this->order->emailOrderActivation();
        }

        // Dispatch an event that the order has been activated
        OrderActivated::dispatch($this->order);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->order->exceptions()->create([
            'action' => 'create',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        // set the order status to failed
        $this->order->update([
            'status' => 'failed',
        ]);

        // dispatch an event that the order activation has failed
        OrderActivationFailed::dispatch($this->order, $exception);
    }
}
