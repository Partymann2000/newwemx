<?php

namespace App\Console\Commands;

use App\Services\WemxGitHubReleases;
use App\Services\WemxReleaseInstaller;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Interactive CLI updater for WemX.
 *
 * Downloads the official GitHub release ZIP and applies it in-place via {@see WemxReleaseInstaller},
 * which is the same pipeline used by the admin web installer. Protected paths (.env, storage, etc.)
 * are never overwritten.
 */
class AppUpdateCommand extends Command
{
    protected $signature = 'app:update
        {--tag= : Install a specific release tag (e.g. v3-beta-0.1.1) without the version picker}
        {--force : Skip the confirmation prompt (useful in scripts; pair with --tag)}';

    protected $description = 'Download and install a WemX release from GitHub';

    public function handle(WemxGitHubReleases $githubReleases, WemxReleaseInstaller $installer): int
    {
        $this->displayHeader($githubReleases);

        $payload = spin(
            callback: fn (): array => $githubReleases->getPayload(forceRefresh: true),
            message: 'Fetching releases from GitHub...',
        );

        if (($payload['error'] ?? null) !== null) {
            error($payload['error']);

            return self::FAILURE;
        }

        $installableReleases = $githubReleases->releasesWithAppBuild($payload['releases']);

        if ($installableReleases === []) {
            error('No releases with a WemX application build were found on GitHub.');

            return self::FAILURE;
        }

        $selectedRelease = $this->resolveRelease($githubReleases, $installableReleases);

        if ($selectedRelease === null) {
            return self::FAILURE;
        }

        $tagName = (string) $selectedRelease['tag_name'];

        if (! $this->confirmUpdate($tagName)) {
            info('Update cancelled.');

            return self::SUCCESS;
        }

        $result = spin(
            callback: fn (): array => $installer->install($selectedRelease),
            message: "Downloading and installing {$tagName}...",
        );

        if (! ($result['success'] ?? false)) {
            error((string) ($result['message'] ?? 'The update failed for an unknown reason.'));

            return self::FAILURE;
        }

        // Refresh cached update status so admin alerts reflect the new version.
        $githubReleases->refreshUpdateStatus();

        info((string) $result['message']);
        $this->displayPostInstallNotes($tagName);

        return self::SUCCESS;
    }

    protected function displayHeader(WemxGitHubReleases $githubReleases): void
    {
        $this->newLine();
        $this->line('  <fg=cyan>WemX in-place update</>');
        $this->line('  Repository: '.WemxGitHubReleases::REPOSITORY);
        $this->line('  Installed:  <fg=yellow>'.$githubReleases->installedVersion().'</>');
        $this->newLine();
    }

    /**
     * @param  list<array<string, mixed>>  $installableReleases
     * @return array<string, mixed>|null
     */
    protected function resolveRelease(WemxGitHubReleases $githubReleases, array $installableReleases): ?array
    {
        $tagOption = trim((string) $this->option('tag'));

        if ($tagOption !== '') {
            $release = $githubReleases->findReleaseByTag($installableReleases, $tagOption);

            if ($release === null) {
                error("Release \"{$tagOption}\" was not found or does not include a WemX build ZIP.");

                return null;
            }

            return $release;
        }

        /** @var array<string, string> $options tag => label for the select dropdown */
        $options = [];

        foreach ($installableReleases as $release) {
            $tag = (string) ($release['tag_name'] ?? '');

            if ($tag === '') {
                continue;
            }

            $options[$tag] = $this->formatReleaseOptionLabel($release);
        }

        $defaultTag = array_key_first($options);

        $selectedTag = select(
            label: 'Which version would you like to install?',
            options: $options,
            default: $defaultTag,
            hint: 'The latest release is selected by default.',
        );

        return $githubReleases->findReleaseByTag($installableReleases, $selectedTag);
    }

    protected function confirmUpdate(string $tagName): bool
    {
        if ($this->option('force')) {
            return true;
        }

        warning('This will replace application files in place.');
        $this->line('  • Your <fg=green>.env</> and <fg=green>storage/</> directory are preserved.');
        $this->line('  • Database migrations are <fg=yellow>not</> run automatically.');
        $this->line('  • Take a backup before updating production systems.');
        $this->newLine();

        return confirm(
            label: "Install release {$tagName} now?",
            default: false,
        );
    }

    /**
     * @param  array<string, mixed>  $release
     */
    protected function formatReleaseOptionLabel(array $release): string
    {
        $tag = (string) ($release['tag_name'] ?? 'unknown');
        $parts = [$tag];

        if ((bool) ($release['prerelease'] ?? false)) {
            $parts[] = 'pre-release';
        }

        if (! empty($release['published_at_human'])) {
            $parts[] = 'published '.$release['published_at_human'];
        }

        $assetName = (string) data_get($release, 'app_build_asset.name', '');

        if ($assetName !== '') {
            $parts[] = $assetName;
        }

        return implode(' · ', $parts);
    }

    protected function displayPostInstallNotes(string $tagName): void
    {
        $this->newLine();
        $this->line('  <fg=green>Update installed:</> '.$tagName);
        $this->newLine();
        $this->line('  Recommended next steps:');
        $this->line('    php artisan migrate --force');
        $this->line('    php artisan config:clear');
        $this->line('    php artisan route:clear');
        $this->line('    php artisan view:clear');
        $this->newLine();
    }
}
