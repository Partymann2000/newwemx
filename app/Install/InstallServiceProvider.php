<?php

namespace App\Install;

use App\Install\Livewire\InstallWizard;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class InstallServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! config('app.installed', false)) {
            config([
                'session.driver' => env('SESSION_DRIVER_INSTALL', 'file'),
                'cache.default' => env('CACHE_STORE_INSTALL', 'file'),
            ]);
        }
    }

    public function boot(): void
    {
        Livewire::component('install-wizard', InstallWizard::class);
        $this->loadViewsFrom(__DIR__.'/views', 'install');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
