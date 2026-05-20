<?php

use App\Services\WemxGitHubReleases;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    public ?string $loadError = null;

    public ?string $fetchedAt = null;

    /** @var list<array<string, mixed>> */
    public array $releases = [];

    /** @var array<string, mixed> */
    public array $status = [];

    public function placeholder()
    {
        return view('admin::updates.releases-timeline-placeholder');
    }

    public function mount(WemxGitHubReleases $githubReleases): void
    {
        $this->loadReleases($githubReleases);
    }

    public function refreshReleases(WemxGitHubReleases $githubReleases): void
    {
        $this->loadReleases($githubReleases, forceRefresh: true);
        $this->dispatch('alert', 'success', 'Release data refreshed from GitHub.');
    }

    protected function loadReleases(WemxGitHubReleases $githubReleases, bool $forceRefresh = false): void
    {
        $payload = $githubReleases->getPayload($forceRefresh);

        $this->loadError = $payload['error'];
        $this->fetchedAt = $payload['fetched_at'];
        $this->releases = $payload['releases'];
        $this->status = $githubReleases->buildStatus($this->releases);
    }

    public function isInstalledRelease(array $release): bool
    {
        $matched = $this->status['matched_release'] ?? null;

        if (! is_array($matched)) {
            return $this->normalizeVersion((string) ($release['tag_name'] ?? ''))
                === $this->normalizeVersion((string) ($this->status['installed_version'] ?? ''));
        }

        return (int) ($release['id'] ?? 0) === (int) ($matched['id'] ?? 0);
    }

    public function isLatestRelease(array $release): bool
    {
        $latestTag = $this->status['latest_tag'] ?? null;

        if ($latestTag === null) {
            return false;
        }

        return $this->normalizeVersion((string) ($release['tag_name'] ?? ''))
            === $this->normalizeVersion((string) $latestTag);
    }

    public function releaseIconClass(array $release): string
    {
        if ($this->isInstalledRelease($release)) {
            return 'bg-green-lt';
        }

        if ($this->isLatestRelease($release)) {
            return 'bg-azure-lt';
        }

        if (! empty($release['prerelease'])) {
            return 'bg-orange-lt';
        }

        return 'bg-secondary-lt';
    }

    public function formatAssetSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }

    protected function normalizeVersion(string $version): string
    {
        return strtolower(ltrim(trim($version), 'vV'));
    }
}

?>

<div>
    @if ($loadError)
        <x-admin::alerts.danger
            title="Could not load releases"
            :message="$loadError"
        />
    @endif

    @if (! $loadError && ($status['update_available'] ?? false))
        <div class="mb-3">
            <x-admin::alerts.info
                title="A newer release is available"
                :message="'You are on ' . ($status['installed_version'] ?? 'unknown') . '. The latest published release is ' . ($status['latest_tag'] ?? 'unknown') . '. Review the changelog below before upgrading.'"
            />
        </div>
    @endif

    @if ($status['is_prerelease_channel'] ?? false)
        <div class="mb-3">
            <x-admin::alerts.warning
                :title="__('Pre-release version') . ' (' . ($status['installed_version'] ?? '') . ')'"
                :message="__('You are running an alpha or beta channel build. Production deployments should use a stable tagged release.')"
            />
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-secondary mb-1">Installed version</div>
                    <div class="h2 mb-1">{{ $status['installed_version'] ?? 'Unknown' }}</div>
                    @if (! empty($status['matched_release']['tag_name']))
                        <div class="text-secondary small">
                            Matched release:
                            <a href="{{ $status['matched_release']['html_url'] }}" target="_blank" rel="noopener noreferrer" class="text-reset">
                                {{ $status['matched_release']['tag_name'] }}
                            </a>
                        </div>
                    @else
                        <div class="text-secondary small">No matching GitHub release tag found for this build.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-secondary mb-1">Latest release</div>
                    <div class="h2 mb-1">{{ $status['latest_tag'] ?? '—' }}</div>
                    @if (! empty($releases[0]['published_at_human']))
                        <div class="text-secondary small">Published {{ $releases[0]['published_at_human'] }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-secondary mb-1">Update status</div>
                    <div class="h2 mb-1">
                        @if ($status['update_available'] ?? false)
                            <span class="badge bg-orange-lt">Update available</span>
                        @elseif (! $loadError)
                            <span class="badge bg-green-lt">Up to date</span>
                        @else
                            <span class="badge bg-secondary-lt">Unknown</span>
                        @endif
                    </div>
                    <div class="text-secondary small">
                        Repository: <a href="{{ WemxGitHubReleases::RELEASES_PAGE_URL }}" target="_blank" rel="noopener noreferrer">{{ WemxGitHubReleases::REPOSITORY }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Release timeline</h3>
            <div class="card-actions btn-list">
                @if ($fetchedAt)
                    <span class="text-secondary small align-self-center">
                        Cached {{ \Carbon\Carbon::parse($fetchedAt)->diffForHumans() }}
                    </span>
                @endif
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    wire:click="refreshReleases"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="refreshReleases">Refresh</span>
                    <span wire:loading wire:target="refreshReleases">Refreshing…</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <ul class="timeline timeline-simple">
                @forelse ($releases as $release)
                    <li class="timeline-event" wire:key="release-{{ $release['id'] }}">
                        <div class="timeline-event-icon {{ $this->releaseIconClass($release) }}">
                            <x-admin::icon icon="package" outline class="icon icon-1"/>
                        </div>
                        <div class="card timeline-event-card @if($this->isInstalledRelease($release)) border border-success @endif">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                    <div>
                                        <h4 class="mb-1">
                                            {{ $release['name'] }}
                                            <code class="ms-1">{{ $release['tag_name'] }}</code>
                                        </h4>
                                        <div class="text-secondary small">
                                            @if (! empty($release['published_at_formatted']))
                                                {{ $release['published_at_formatted'] }}
                                                @if (! empty($release['published_at_human']))
                                                    ({{ $release['published_at_human'] }})
                                                @endif
                                            @endif
                                            @if (! empty($release['author_login']))
                                                · by {{ $release['author_login'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 align-items-start">
                                        @if ($this->isInstalledRelease($release))
                                            <span class="badge bg-green-lt">Your version</span>
                                        @endif
                                        @if ($this->isLatestRelease($release))
                                            <span class="badge bg-azure-lt">Latest</span>
                                        @endif
                                        @if (! empty($release['prerelease']))
                                            <span class="badge bg-orange-lt">Pre-release</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    @if (trim((string) ($release['body'] ?? '')) !== '')
                                        <div class="markdown small" style="max-height: min(40vh, 320px); overflow-y: auto;">
                                            {!! Str::markdown($release['body']) !!}
                                        </div>
                                    @else
                                        <p class="text-secondary mb-0 small">No changelog provided for this release.</p>
                                    @endif
                                </div>

                                @if (! empty($release['assets']))
                                    <div class="mb-3">
                                        <div class="text-secondary small mb-1">Downloads</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($release['assets'] as $asset)
                                                <a
                                                    href="{{ $asset['browser_download_url'] }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-sm btn-outline-secondary"
                                                >
                                                    {{ $asset['name'] }}
                                                    <span class="text-secondary ms-1">({{ $this->formatAssetSize((int) $asset['size']) }})</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-2">
                                    @if (! empty($release['html_url']))
                                        <a href="{{ $release['html_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                                            View on GitHub
                                        </a>
                                    @endif
                                    @if (! empty($release['zipball_url']))
                                        <a href="{{ $release['zipball_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                            Source (zip)
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="text-secondary">
                        @if ($loadError)
                            No releases to display.
                        @else
                            No published releases found for this repository yet.
                        @endif
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
