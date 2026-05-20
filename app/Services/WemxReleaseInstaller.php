<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class WemxReleaseInstaller
{
    public const LOCK_KEY = 'wemx.release-install';

    public const LOCK_SECONDS = 600;

    /**
     * Paths relative to project root that must never be overwritten during an in-place update.
     *
     * @var list<string>
     */
    public const PROTECTED_PATHS = [
        '.env',
        'storage',
        'database/database.sqlite',
        '.git',
    ];

    /**
     * @param  array<string, mixed>  $release
     * @return array{success: bool, message: string, tag?: string}
     */
    public function install(array $release): array
    {
        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_SECONDS);

        if (! $lock->get()) {
            return [
                'success' => false,
                'message' => 'Another release installation is already in progress. Please wait and try again.',
            ];
        }

        try {
            return $this->performInstall($release);
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $release
     * @return array{success: bool, message: string, tag?: string}
     */
    protected function performInstall(array $release): array
    {
        set_time_limit(300);

        $releaseId = (int) ($release['id'] ?? 0);
        $tagName = (string) ($release['tag_name'] ?? '');

        if ($releaseId === 0 || $tagName === '') {
            return [
                'success' => false,
                'message' => 'Invalid release metadata.',
            ];
        }

        $asset = app(WemxGitHubReleases::class)->primaryAppBuildAsset($release);

        if ($asset === null) {
            return [
                'success' => false,
                'message' => 'No application build ZIP was found for this release. Ensure the GitHub release includes a WemX-*.zip asset.',
            ];
        }

        $downloadUrl = (string) ($asset['browser_download_url'] ?? '');

        if (! $this->isAllowedDownloadUrl($downloadUrl)) {
            return [
                'success' => false,
                'message' => 'The release download URL is not from an allowed GitHub source.',
            ];
        }

        $tmpZipPath = storage_path("app/updates/tmp_zips/release_{$releaseId}.zip");
        $tempExtractPath = storage_path("app/updates/tmp_extract/release_{$releaseId}");

        try {
            $this->downloadAsset($downloadUrl, $tmpZipPath);
            $sourcePath = $this->extractRelease($tmpZipPath, $tempExtractPath);
            $this->copyToProjectRoot($sourcePath);

            return [
                'success' => true,
                'message' => "Successfully installed {$tagName}. Your .env and storage data were preserved. Review the release changelog, run migrations if required (`php artisan migrate --force`), and clear caches if you notice stale config or views.",
                'tag' => $tagName,
            ];
        } catch (RuntimeException $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } finally {
            @File::delete($tmpZipPath);
            if (is_dir($tempExtractPath)) {
                File::deleteDirectory($tempExtractPath);
            }
        }
    }

    protected function downloadAsset(string $url, string $destination): void
    {
        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if (is_file($destination)) {
            @unlink($destination);
        }

        $response = Http::timeout(300)
            ->withHeaders([
                'Accept' => 'application/octet-stream',
                'User-Agent' => (string) config('app.name', 'WemX'),
            ])
            ->sink($destination)
            ->get($url);

        if (! $response->successful() || ! is_file($destination) || filesize($destination) === 0) {
            @unlink($destination);

            throw new RuntimeException('Failed to download the release build. The host may be unreachable or the asset no longer exists.');
        }
    }

    protected function extractRelease(string $zipPath, string $extractPath): string
    {
        if (is_dir($extractPath)) {
            File::deleteDirectory($extractPath);
        }

        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Failed to open the release ZIP. The file may be corrupted.');
        }

        if (! $zip->extractTo($extractPath)) {
            $zip->close();

            throw new RuntimeException('Failed to extract the release ZIP into a temporary directory.');
        }

        $zip->close();

        $items = array_values(array_diff(scandir($extractPath) ?: [], ['.', '..']));

        if ($items === []) {
            throw new RuntimeException('The extracted release archive is empty.');
        }

        $first = $extractPath.DIRECTORY_SEPARATOR.$items[0];

        if (is_dir($first) && count($items) === 1) {
            return $first;
        }

        return $extractPath;
    }

    protected function copyToProjectRoot(string $sourcePath): void
    {
        $destination = base_path();
        $sourcePath = rtrim($sourcePath, DIRECTORY_SEPARATOR);

        if (! is_dir($sourcePath)) {
            throw new RuntimeException('The extracted release source path is invalid.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace('\\', '/', substr($item->getPathname(), strlen($sourcePath) + 1));

            if ($this->isProtectedPath($relativePath)) {
                continue;
            }

            $target = $destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }

                continue;
            }

            $targetDirectory = dirname($target);

            if (! is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            if (! copy($item->getPathname(), $target)) {
                throw new RuntimeException("Failed to copy release file to {$relativePath}. Check filesystem permissions.");
            }
        }
    }

    public function isProtectedPath(string $relativePath): bool
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        foreach (self::PROTECTED_PATHS as $protected) {
            $protected = ltrim($protected, '/');

            if ($relativePath === $protected || str_starts_with($relativePath, $protected.'/')) {
                return true;
            }
        }

        return false;
    }

    protected function isAllowedDownloadUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($host) || ! is_string($path)) {
            return false;
        }

        return in_array($host, ['github.com', 'objects.githubusercontent.com'], true)
            && str_contains($path, '/'.WemxGitHubReleases::REPOSITORY.'/');
    }
}
