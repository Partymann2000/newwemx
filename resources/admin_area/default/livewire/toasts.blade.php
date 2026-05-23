<?php

use App\Models\AppTaskLog;
use App\Services\WemxGitHubReleases;
use Livewire\Volt\Component;

new class extends Component
{
    public function mount(): void
    {
        if (AppTaskLog::isSchedularRunning()) {
            session()->forget('admin_dismiss_cron_toast');
        }

        if (AppTaskLog::isQueueWorkerRunning()) {
            session()->forget('admin_dismiss_queue_toast');
        }

        if (! admin_is_prerelease_version()) {
            session()->forget('admin_dismiss_prerelease_toast');
        }

        if (! $this->hasGitHubUpdate()) {
            session()->forget('admin_dismiss_update_toast');
        }
    }

    public function hasGitHubUpdate(): bool
    {
        return app(WemxGitHubReleases::class)->hasUpdateAvailable();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function githubUpdateStatus(): ?array
    {
        return app(WemxGitHubReleases::class)->getCachedUpdateStatus();
    }

    public function isPrereleaseVersion(): bool
    {
        return admin_is_prerelease_version();
    }

    public function closePrereleaseToast(): void
    {
        session()->put('admin_dismiss_prerelease_toast', true);
    }

    public function closeCronToast(): void
    {
        session()->put('admin_dismiss_cron_toast', true);
    }

    public function closeQueueToast(): void
    {
        session()->put('admin_dismiss_queue_toast', true);
    }

    public function closeUpdateToast(): void
    {
        session()->put('admin_dismiss_update_toast', true);
    }
}

?>


<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">

    @if(false)
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="toast-header">
            <strong class="me-auto">Update Available</strong>
            <small>11 mins ago</small>
        </div>
        <div class="toast-body">
            There is a new version of WemX available for download.
            <div class="mt-2 pt-2 border-top">
                <button type="button" class="btn btn-primary" style="padding: 6px 12px;">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-refresh"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                    Update
                </button>
                <button type="button" class="btn btn-secondary"  style="padding: 6px 12px;">Learn More</button>
            </div>
        </div>
    </div>
    @endif

    @if($this->isPrereleaseVersion() && ! session('admin_dismiss_prerelease_toast'))
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="toast-header">
            <strong class="me-auto">{{ __('Pre-release version') }}</strong>
            <small>{{ config('app.version') }}</small>
            <button type="button" wire:click="closePrereleaseToast" class="ms-2 btn-close" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ __('You are running an alpha or beta release. Do not use this build for production workloads; data loss and breaking changes are possible.') }}
            <div class="mt-2 pt-2 border-top">
                <button type="button" class="btn btn-primary"  style="padding: 6px 12px;">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-external-link"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                    Learn More
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(! AppTaskLog::isSchedularRunning() && ! session('admin_dismiss_cron_toast'))
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="toast-header">
            <strong class="me-auto">Cron jobs are not running</strong>
            <small>{{ AppTaskLog::lastSchedularRun() }}</small>
            <button type="button" wire:click="closeCronToast" class="ms-2 btn-close" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Last run {{ AppTaskLog::lastSchedularRun() }}. If this message persists, please check your server's cron job configuration.
            <div class="mt-2 pt-2 border-top">
                <button type="button" class="btn btn-primary"  style="padding: 6px 12px;">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-external-link"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                    Learn More
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($this->hasGitHubUpdate() && ! session('admin_dismiss_update_toast'))
    @php($updateStatus = $this->githubUpdateStatus())
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="toast-header">
            <strong class="me-auto">{{ __('Update available') }}</strong>
            <small>{{ $updateStatus['latest_tag'] ?? '' }}</small>
            <button type="button" wire:click="closeUpdateToast" class="ms-2 btn-close" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {!! __('A new version of WemX is available. You are running <code>:current</code> and <code>:latest</code> is available.', [
                'current' => $updateStatus['installed_version'] ?? config('app.version'),
                'latest' => $updateStatus['latest_tag'] ?? __('unknown'),
            ]) !!}
            <div class="mt-2 pt-2 border-top">
                <a href="{{ route('admin.updates.index') }}" wire:navigate class="btn btn-primary" style="padding: 6px 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-refresh"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                    {{ __('View updates') }}
                </a>
            </div>
        </div>
    </div>
    @endif

    @if(! AppTaskLog::isQueueWorkerRunning() && ! session('admin_dismiss_queue_toast'))
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="toast-header">
            <strong class="me-auto">Queue worker is not running</strong>
            <small>{{ now()->diffForHumans() }}</small>
            <button type="button" wire:click="closeQueueToast" class="ms-2 btn-close" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            The queue worker is not running. Please ensure that the queue worker is started to process background jobs.
            <div class="mt-2 pt-2 border-top">
                <button type="button" class="btn btn-primary"  style="padding: 6px 12px;">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-external-link"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                    Learn More
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
