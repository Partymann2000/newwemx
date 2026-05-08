<?php

namespace App\Extensions\Commands;

use App\Extensions\Traits\CommandHelper;
use Illuminate\Console\Command;
class ExtensionCommand extends Command
{
    use CommandHelper;
    protected $signature = 'extension';

    protected $description = 'Manage extensions through available subcommands';

    public function handle(): void
    {
        $commands = [
            'List Extensions' => 'extension:list',
            'Enable Extension' => 'extension:enable',
            'Disable Extension' => 'extension:disable',
            'Install Extension' => 'extension:install',
            'Uninstall Extension' => 'extension:uninstall',
            'Publish Extension' => 'extension:publish',
            'Discover Extension' => 'extension:discover',
            'Migrate Extension' => 'extension:migrate',
        ];

        $choice = $this->choice(
            'Which command would you like to run?',
            array_keys($commands)
        );
        $commandToRun = $commands[$choice];
        $this->call($commandToRun);
    }
}
