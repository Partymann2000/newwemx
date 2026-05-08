<?php

namespace App\Traits\Models;

use App\Facades\ModelSettings;
use App\Models\ModelSetting;

trait HasSettings
{
    protected ModelSettings $settingsManager;

    public function settings()
    {
        return $this->morphMany(ModelSetting::class, 'model');
    }

    public function setting(?string $key = null, $default = null)
    {
        if (!isset($this->settingsManager)) {
            $this->settingsManager = new ModelSettings($this);
        }

        if ($key) {
            return $this->settingsManager->get($key, $default);
        }

        return $this->settingsManager;
    }
}
