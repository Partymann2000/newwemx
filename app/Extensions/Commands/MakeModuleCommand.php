<?php

namespace App\Extensions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Extension;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name? : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt for module name if not provided
        $name = $this->argument('name');

        if (!$name) {
            $name = $this->ask('What is the name of the module?');
        }

        // Define the modules lowercase name
        $lowerName = strtolower($name);

        // Convert the module name to StudlyCase
        $name = Str::studly($name);

        // Define the module directory path
        $modulePath = base_path("extensions/modules/{$name}");

        // Check if the module already exists
        if (is_dir($modulePath)) {
            $this->error("The module {$name} already exists!");
            return Command::FAILURE;
        }

        $description = $this->ask('What is the description of the module?', 'Example module for the extension system.');

        $authorName = $this->ask('What is the name of the author?', 'John Doe');

        $authorEmail = $this->ask('What is the email of the author?', 'example@gmail.com');

        // ensure the module name does not have spaces, special characters, or numbers and is minimum 3 characters and maximum 50 characters using Validator
        $validator = Validator::make(['name' => $name, 'description' => $description, 'author_name' => $authorName, 'author_email' => $authorEmail], [
            'name' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii'],
            'description' => ['required', 'string', 'max:150'],
            'author_name' => ['required', 'string', 'min:2', 'max:50'],
            'author_email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            foreach($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Create the module directory
        mkdir($modulePath, 0755, true);

        // Define the path for the Module.php file
        $moduleFile = "{$modulePath}/Module.php";

        // Stub content
        $stub = file_get_contents(__DIR__ . '/stubs/module.stub');

        // Replace placeholders in the stub
        $content = str_replace(
            ['{{extension_name}}', '{{namespace}}', '{{extension_lower_name}}', '{{extension_description}}', '{{author_name}}', '{{author_email}}'],
            [$name, "Extensions\\Modules\\{$name}", $lowerName, $description, $authorName, $authorEmail],
            $stub
        );

        // Create the Module.php file
        file_put_contents($moduleFile, $content);

        // update extensions
        Extension::discover();

        $this->info("Module {$name} created successfully at {$modulePath}.");
        return Command::SUCCESS;
    }
}
