<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class AddExtensionMigrations extends Command
{
    use CommandHelper;

    protected $signature = 'extensions:add-migrations {name?}';
    protected $description = 'Make migrations for the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        if($extension->extension()->hasMigrations()) {
            $this->error("Migrations already exist for '{$extension->extension()->getName()}' in '{$extension->extension()->getMigrationsPath()}'");
            return;
        }

        $this->info("Making migrations for '{$extension->extension()->getName()}'...");
        $extension->extension()->makeMigrations();
        $this->info("Migrations created for '{$extension->extension()->getName()}' in '{$extension->extension()->getMigrationsPath()}'.");
    }
}
