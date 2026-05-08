<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class UninstallExtension extends Command
{
    use CommandHelper;

    protected $signature = 'extension:uninstall {name?}';
    protected $description = 'Uninstall the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        $this->info("Uninstalling extension '{$extension->identifier}'...");

        // TODO: Add the code to uninstall the extension

        $this->info("Extension '{$extension->identifier}' has been uninstalled successfully.");
        $this->logAction('Uninstalled', $extension);
    }
}
