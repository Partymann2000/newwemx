<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;

class ListExtensions extends Command
{
    use CommandHelper;

    protected $signature = 'extension:list';
    protected $description = 'List all available extensions';

    public function handle(): void
    {
        $extensions = $this->extensionClass()->all();
        if ($extensions->isEmpty()) {
            $this->info("No extensions found.");
            return;
        }

        $headers = ['Id', 'Name', 'Type', 'Status'];
        $data = [];

        foreach ($extensions as $extension) {
            $status = $extension->status === 'enabled' ? "<info>{$extension->status}</info>" : "<error>{$extension->status}</error>";
            $data[] = [$extension->identifier, "<comment>{$extension->name}</comment>", $extension->type, $status];
        }
        $this->table($headers, $data);
    }
}
