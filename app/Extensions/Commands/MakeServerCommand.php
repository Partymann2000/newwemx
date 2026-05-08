<?php

namespace App\Extensions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Extension;

class MakeServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:server {name? : The name of the server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new server.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt for server name if not provided
        $name = $this->argument('name');

        if (!$name) {
            $name = $this->ask('What is the name of the server?');
        }

        // Define the servers lowercase name
        $lowerName = strtolower($name);

        // Convert the servers name to StudlyCase
        $name = Str::studly($name);

        // Define the module directory path
        $serverPath = base_path("extensions/servers/{$name}");

        // Check if the module already exists
        if (is_dir($serverPath)) {
            $this->error("The server {$name} already exists!");
            return Command::FAILURE;
        }

        $description = $this->ask('What is the description of the server?', 'Example server for the extension system.');

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
        mkdir($serverPath, 0755, true);

        // Define the path for the Module.php file
        $serverFile = "{$serverPath}/Server.php";

        // Stub content
        $stub = file_get_contents(__DIR__ . '/stubs/server.stub');

        // Replace placeholders in the stub
        $content = str_replace(
            ['{{extension_name}}', '{{namespace}}', '{{extension_lower_name}}', '{{extension_description}}', '{{author_name}}', '{{author_email}}'],
            [$name, "Extensions\\Servers\\{$name}", $lowerName, $description, $authorName, $authorEmail],
            $stub
        );

        // Create the Module.php file
        file_put_contents($serverFile, $content);

        // update extensions
        Extension::discover();

        $this->info("Server {$name} created successfully at {$serverPath}.");
        return Command::SUCCESS;
    }
}
