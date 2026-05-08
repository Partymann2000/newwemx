<?php

namespace App\Console\Commands\Cronjobs;

use Illuminate\Console\Command;
use App\Models\Subscription;

class CheckSubscriptionsCommand extends Command
{
    protected $signature = 'cronjobs:check-subscriptions';

    protected $description = 'Check and update the status of subscriptions';

    public function handle(): void
    {
        $subscriptions = Subscription::whereIn('status', ['active', 'cancelled'])->get();

        foreach ($subscriptions as $subscription) {
            $subscription->check();
        }
    }
}
