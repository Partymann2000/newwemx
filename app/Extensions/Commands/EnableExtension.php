<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class EnableExtension extends Command
{
    use CommandHelper;

    protected $signature = 'extension:enable {name?} {--force?}';
    protected $description = 'Enable the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;
        $this->executeExtensionCommand($extension, true, 'Enabling');
    }
}
