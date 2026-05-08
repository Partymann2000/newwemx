<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class DisableExtension extends Command
{
    use CommandHelper;

    protected $signature = 'extension:disable {name?}';
    protected $description = 'Disable the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;
        $this->executeExtensionCommand($extension, false, 'Disabling');
    }
}
