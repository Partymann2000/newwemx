<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class DiscoverExtension extends Command
{
    use CommandHelper;

    protected $signature = 'extension:discover';

    protected $description = 'Discover all extensions';

    public function handle(): void
    {
        $this->info("Discover all extensions...");
        $this->extensionClass()->discover();
    }
}
