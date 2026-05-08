<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class AddExtensionViews extends Command
{
    use CommandHelper;

    protected $signature = 'extensions:add-views {name?}';
    protected $description = 'Make views for the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        if($extension->extension()->hasViews()) {
            $this->error("Views already exist for '{$extension->extension()->getName()}' in '{$extension->extension()->getViewsPath()}'");
            return;
        }

        $this->info("Making views for '{$extension->extension()->getName()}'...");
        $extension->extension()->makeViews();
        $this->info("Views created for '{$extension->extension()->getName()}' in '{$extension->extension()->getViewsPath()}'.");
    }
}
