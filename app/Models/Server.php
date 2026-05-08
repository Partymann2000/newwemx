<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Server extends Extension
{
    /**
     * The "booted" method of the model.
     *
     * This is where we add the global scope.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('server', function (Builder $builder) {
            $builder->where('type', 'server');
        });
    }

    public function getConfig()
    {
        return collect($this->extension()->setConfig());
    }

    public function getPackageConfig($package)
    {
        return collect($this->extension()->setPackageConfig($package, $package->serverConnection));
    }

    public function getConfigRules(string $prefix = ''): array
    {
        return $this->getConfig()->mapWithKeys(function ($config) use ($prefix) {
            return [$prefix . $config['key'] => $config['rules']];
        })->toArray();
    }

    public function getPackageConfigRules($package, string $prefix = ''): array
    {
        return $this->getPackageConfig($package)->mapWithKeys(function ($config) use ($prefix) {
            return [$prefix . $config['key'] => $config['rules']];
        })->toArray();
    }

    public function getPackageConfigOptions($package)
    {
        $packageConfigOptions = $this->getPackageConfig($package);

        // get all options where is_configurable is true, return original array
        return $packageConfigOptions->filter(function ($option) {
            return (isset($option['is_configurable']) && $option['is_configurable'] === true) ? $option : null;
        })->values()->toArray();

    }

    public function hasTestConnection(): bool
    {
        return method_exists($this->namespace, 'testConnection');
    }

    public function testConnection(array $config)
    {
        if(!$this->hasTestConnection()) {
            return true;
        }

        return $this->extension()->testConnection($config);
    }
}
