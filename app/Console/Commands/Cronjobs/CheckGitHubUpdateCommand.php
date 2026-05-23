<?php

namespace App\Console\Commands\Cronjobs;

use App\Services\WemxGitHubReleases;
use Illuminate\Console\Command;

class CheckGitHubUpdateCommand extends Command
{
    protected $signature = 'cronjobs:check-github-update';

    protected $description = 'Check GitHub for new updates for WemX (github.com/wemxnet/wemx)';

    public function handle(WemxGitHubReleases $githubReleases): int
    {
        $status = $githubReleases->refreshUpdateStatus();

        if (($status['error'] ?? null) !== null) {
            $this->warn('GitHub update check completed with an error: '.$status['error']);

            return self::FAILURE;
        }

        if ($status['update_available'] ?? false) {
            $this->info(sprintf(
                'Update available: %s → %s',
                $status['installed_version'],
                $status['latest_tag'] ?? __('unknown')
            ));

            return self::SUCCESS;
        }

        $this->info('Application is up to date ('.$status['installed_version'].').');

        return self::SUCCESS;
    }
}
