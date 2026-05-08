<?php

namespace App\Jobs\Orders;

use App\Events\Orders\Errors\OrderTerminationFailed;
use App\Events\Orders\OrderTerminated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Order;
use Throwable;

class OrderTerminateServer implements ShouldQueue
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
        // Here we will attempt to terminate the server for the order
        $connection = $this->order->package->serverConnection;
        $this->order->serverMethods()->terminate($this->order, $connection);

        // Mark the order as terminated
        $this->order->update([
            'status' => 'terminated',
        ]);

        // notify the user about the termination
        $this->order->emailOrderTermination();

        // dispatch an event that the order has been terminated
        OrderTerminated::dispatch($this->order);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->order->exceptions()->create([
            'action' => 'terminate',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        // set the order status to failed
        $this->order->update([
            'status' => 'failed',
        ]);

        // Dispatch an event that the order termination has failed
        OrderTerminationFailed::dispatch($this->order, $exception);
    }
}
