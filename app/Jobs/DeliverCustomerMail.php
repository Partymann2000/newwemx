<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerMail;
use App\Models\Email;
use Throwable;

class DeliverCustomerMail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Email $email,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        config(['mail.from.name' => settings('app_name', 'My Application')]);

        Mail::to($this->email->to)->send(new CustomerMail($this->email));
        $this->email->markAsDelivered();
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        // mark email as failed in the system
        $this->email->markAsFailed();
    }
}
