<?php

namespace App\Facades;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Utils
{
    /**
     * Check the scheduler status and cache it for 5 minutes.
     *
     * @return bool
     */
    public function checkStatus(): bool
    {
        // Use the cache or get a new status and store it in the cache
        return Cache::remember('scheduler_status', now()->addMinutes(5), function () {
            $path = storage_path('scheduler_heartbeat.txt');
            // Check if the heartbeat file exists
            if (File::exists($path)) {
                $lastRunTime = File::get($path);
                $lastRunTime = Carbon::parse($lastRunTime);
                // If the last run was less than 10 minutes ago
                return now()->diffInMinutes($lastRunTime) <= 10;
            }
            return false;
        });
    }

    public static function getAllLocales(): array
    {
        return Cache::remember('all_locales', 3600, function () {
            $locales = [];
            $langPath = base_path('lang');
            if (File::exists($langPath) && File::isDirectory($langPath)) {
                $directories = File::directories($langPath);
                foreach ($directories as $directory) {
                    $locales[] = File::name($directory);
                }
            }

            return $locales;
        });
    }
}
