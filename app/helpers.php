<?php

if (!function_exists('asset_with_version')) {
    function asset_with_version($area, $path = ''): string
    {
        $theme = config('app.theme', 'default');
        $version = config('app.version');
        $assetPath = "/assets/{$area}/{$theme}/{$path}";
        $separator = str_contains($assetPath, '?') ? '&' : '?';
        return "{$assetPath}{$separator}v={$version}";
    }
}

if (!function_exists('admin_asset')) {
    function admin_asset($path = ''): string
    {
        return asset_with_version('adminarea', $path);
    }
}

if (!function_exists('client_asset')) {
    function client_asset($path = ''): string
    {
        return asset_with_version('clientarea', $path);
    }
}


if (!function_exists('isJson')) {
    function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}


if (!function_exists('extensionElements')) {
    function extensionElements(string|array $element): array
    {
        return \App\Models\ExtensionElement::activeElements($element);
    }
}

if (!function_exists('settings')) {
    /**
     * Get or set the setting value.
     *
     * @param string|null $key Setup key. If null, returns all settings.
     * @param mixed|null $default Default value to get.
     * @return mixed|\App\Models\Setting
     */
    function settings(string|array $key, mixed $default = null): mixed
    {
        // if the key is an array, then we set the settings
        if (is_array($key)) {
            return \App\Models\Setting::store($key);
        }

        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('extensions')) {
    function extensions(): \App\Models\Extension
    {
        return app(\App\Models\Extension::class);
    }
}

if (!function_exists('admin_view')) {
    /**
     * @param string $view
     * @param array $data
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function admin_view(string $view, array $data = []): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return dynamic_view('admin', $view, $data);
    }
}

if (!function_exists('client_view')) {
    /**
     * @param string $view
     * @param array $data
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function client_view(string $view, array $data = []): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return dynamic_view('client', $view, $data);
    }
}

if (!function_exists('dynamic_view')) {
    /**
     * @param string $area - Area name (admin or client)
     * @param string $view - Template name (may include module via '::')
     * @param array $data - Template data
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function dynamic_view(string $area, string $view, array $data = []): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        // Define the theme (if it is admin, use config('app.admin_theme'))
        $theme = $area === 'admin' ? config('app.admin_theme', 'default') : config('app.theme', 'default');
        // We check whether the specified view contains the module
        if (str_contains($view, '::')) {
            [$module, $viewName] = explode('::', $view);
            // FIRST STEP: we check if the template exists in the current theme (for the module)
            if (view()->exists("{$area}::{$theme}.{$module}.{$viewName}")) {
                return view("{$area}::{$theme}.{$module}.{$viewName}", $data);
            }
            // SECOND STEP: if not in the topic, check the standard location of the module in admin or client
            if (view()->exists("{$module}::{$area}_area.{$theme}.{$viewName}")) {
                return view("{$module}::{$area}_area.{$theme}.{$viewName}", $data);
            }
            abort(404, "View [{$area}::{$theme}.{$module}.{$viewName}] or [{$module}::{$area}_area.{$theme}.{$viewName}] not found.");
        }

        // Check templates without module prefix
        if (view()->exists("{$area}::{$view}")) {
            return view("{$area}::{$view}", $data);
        }
        abort(404, "View [{$area}::{$view}] not found.");
    }
}

if (!function_exists('utils')) {
    function utils(): \App\Facades\Utils
    {
        return app(\App\Facades\Utils::class);
    }
}

if (!function_exists('admin_view_path')) {
    function admin_view_path(string $path = null)
    {
        if(!$path) {
            return "admin_area.default";
        }

        return "admin_area.default.{$path}";
    }
}

if (!function_exists('client_view_path')) {
    function client_view_path(string $path = null)
    {
        if(!$path) {
            return "client_area.default";
        }

        return "client_area.default.{$path}";
    }
}

if (!function_exists('cart')) {
    function cart()
    {
        return request()->cart;
    }
}

if (!function_exists('baseCurrency')) {
    function baseCurrency()
    {
        return settings('currency', 'USD');
    }
}

if (!function_exists('activeCurrency')) {
    function activeCurrency()
    {
        return session('currency', baseCurrency());
    }
}

if (!function_exists('price')) {
    function price(int|float $amount, string $to = '', string $in = '', ?string $locale = null, bool $absolute = false)
    {
        if(!$to) {
            $to = activeCurrency();
        }

        if(!$in) {
            $baseCurrency = baseCurrency();
        } else {
            $baseCurrency = $in;
        }

        $convertedAmount = \App\Models\Currency::convert($amount, $baseCurrency, $to);

        if($absolute) {
            return $convertedAmount;
        }

        return \Illuminate\Support\Number::currency($convertedAmount, $to, $locale);
    }
}

if (!function_exists('priceIn')) {
    function priceIn(int|float $amount, string $currency = '',  ?string $locale = null, bool $absolute = false)
    {
        return price($amount, $currency, $currency, $locale, $absolute);
    }
}

if (!function_exists('daysToPeriod')) {
    function daysToPeriod(int $days = 0)
    {
        if ($days === 0) {
            return __('messages.one_time');
        }

        if($days === 1) {
            return __('messages.daily');
        }

        if($days === 7) {
            return __('messages.weekly');
        }

        if($days === 14) {
            return __('messages.biweekly');
        }

        if($days === 30) {
            return __('messages.monthly');
        }

        if($days === 90) {
            return __('messages.quarterly');
        }

        if($days === 180) {
            return __('messages.semi_annually');
        }

        if($days === 365) {
            return __('messages.annually');
        }

        return __('messages.every_x_days', ['days' => $days]);
    }
}

if (! function_exists('admin_is_prerelease_version')) {
    function admin_is_prerelease_version(): bool
    {
        return (bool) preg_match('/(?<![a-z])(?:alpha|beta)(?![a-z])/i', (string) config('app.version', ''));
    }
}