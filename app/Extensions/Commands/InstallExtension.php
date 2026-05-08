<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallExtension extends Command
{
    use CommandHelper;

    protected $signature = 'extension:install {name?}';
    protected $description = 'Install the specified extension';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));

        if (!$name) {
            $extensions = $this->extensionClass()->all();
            $name = $this->choice('Which extension would you like to install?', $extensions->pluck('identifier')->toArray());
        }

        $this->info("Installing extension '{$name}'...");
        $this->call('extension:enable', ['name' => $name, '--force' => true]);
        $this->call('extension:migrate', ['name' => $name]);
        $this->call('extension:publish', ['name' => $name, '--assets' => true]);
        $this->info("Extension '{$name}' has been installed successfully.");
        $this->logAction('Installed', $name);
    }
}
