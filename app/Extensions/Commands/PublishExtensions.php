<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PublishExtensions extends Command
{
    use CommandHelper;
    protected $signature = 'extension:publish {name?} {--config} {--assets}';
    protected $description = 'Publish resources (config, assets) for the specified extension';

    public function handle(): void
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->publishForAllExtensions();
        } else {
            $this->publishForSingleExtension(Str::lower($name));
        }
    }

    protected function publishForAllExtensions(): void
    {

        foreach ($this->extensionClass()->all() as $extension) {
            $this->publishResources($extension->identifier);
        }
    }

    protected function publishForSingleExtension($name): void
    {
        $this->publishResources($name);
    }

    protected function publishResources($name): void
    {
        if ($this->option('config')) {
            $this->publishConfig($name);
        }
        if ($this->option('assets') || (!$this->option('config') && !$this->option('assets'))) {
            $this->publishAssets($name);
        }
    }

    protected function publishConfig($name): void
    {
        $this->info("Publishing configuration for {$name}");
        $this->call('vendor:publish', ['--tag' => "{$name}-config", '--force' => true]);
        $this->logAction('Published config', $name);
    }

    protected function publishAssets($name): void
    {
        $this->info("Publishing assets for {$name}");
        $this->call('vendor:publish', ['--tag' => "{$name}-assets", '--force' => true]);
        $this->logAction('Published assets', $name);
    }
}
