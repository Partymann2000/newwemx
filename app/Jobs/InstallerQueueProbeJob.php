<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class InstallerQueueProbeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $probeKey,
    ) {}

    public function handle(): void
    {
        Cache::put($this->probeKey, now()->toDateTimeString(), now()->addMinutes(5));
    }
}
