<?php

namespace App\Facades;

use Illuminate\Database\Eloquent\Model;

class ModelSettings
{
    protected Model $model;
    protected array $cache = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->load();
    }

    protected function load(): void
    {
        $this->cache = $this->model->settings()->pluck('value', 'key')->toArray();
    }

    public function get(string $key, $default = null)
    {
        if (str_contains($key, 'encrypt')) {
            return isset($this->cache[$key]) ? decrypt($this->cache[$key]) : $default;
        }

        return $this->cache[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        if (str_contains($key, 'encrypt')) {
            $value = encrypt($value);
        }

        $this->model->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $this->cache[$key] = $value;
    }

    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getMany(array $keys): array
    {
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = $this->get($key);
        }

        return $settings;
    }

    public function delete(string $key): bool
    {
        $deleted = $this->model->settings()->where('key', $key)->delete();

        if ($deleted) {
            unset($this->cache[$key]);
        }

        return $deleted;
    }

    public function all(): array
    {
        return $this->cache;
    }
}
