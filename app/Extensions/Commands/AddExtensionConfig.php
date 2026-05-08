<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class AddExtensionConfig extends Command
{
    use CommandHelper;

    protected $signature = 'extensions:add-config {name?}';
    protected $description = 'Make config for the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        if($extension->extension()->hasConfig()) {
            $this->error("Config already exist for '{$extension->extension()->getName()}' in '{$extension->extension()->getConfigPath()}'");
            return;
        }

        $this->info("Making config for '{$extension->extension()->getName()}'...");
        $extension->extension()->makeConfig();
        $this->info("Config created for '{$extension->extension()->getName()}' in '{$extension->extension()->getConfigPath()}'.");
    }
}
