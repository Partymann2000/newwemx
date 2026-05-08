<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class AddExtensionRoutes extends Command
{
    use CommandHelper;

    protected $signature = 'extensions:add-routes {name?}';
    protected $description = 'Make routes for the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        if($extension->extension()->hasRoutes()) {
            $this->error("Routes already exist for '{$extension->extension()->getName()}' in '{$extension->extension()->getRoutesPath()}'");
            return;
        }

        $this->info("Making routes for '{$extension->extension()->getName()}'...");
        $extension->extension()->makeRoutes();
        $this->info("Routes created for '{$extension->extension()->getName()}' in '{$extension->extension()->getRoutesPath()}'.");
    }
}
