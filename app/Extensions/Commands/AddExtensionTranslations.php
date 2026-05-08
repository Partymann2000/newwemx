<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class AddExtensionTranslations extends Command
{
    use CommandHelper;

    protected $signature = 'extensions:add-translations {name?}';
    protected $description = 'Make translations for the specified extension';

    public function handle(): void
    {
        $extension = $this->getExtensions($this->argument('name'));
        if (!$extension) return;

        if($extension->extension()->hasTranslations()) {
            $this->error("Translations exist for '{$extension->extension()->getName()}' in '{$extension->extension()->getTranslationsPath()}'");
            return;
        }

        $this->info("Making translations for '{$extension->extension()->getName()}'...");
        $extension->extension()->makeTranslations();
        $this->info("Translations created for '{$extension->extension()->getName()}' in '{$extension->extension()->getTranslationsPath()}'.");
    }
}
