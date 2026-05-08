<?php

namespace App\Extensions\Traits;

use App\Models\Extension;
use Illuminate\Support\Str;

trait CommandHelper
{
    public function extensionClass(): Extension
    {
        return app(Extension::class);
    }
    public function getExtensions($name = null, $ask = true)
    {
        if (!$name && $ask) {
            $name = $this->ask('What is the name of the extension?');
        }
        $name = Str::lower($name);
        $extension = $this->extensionClass()->where('identifier', $name)->first();
        if (!$extension) {
            $this->error("Extension '{$name}' not found.");
            $extension = $this->extensionClass()->where('identifier', 'like', "%{$name}%")->orWhere('name', 'like', "%{$name}%")->first();
            if ($extension) {
                if (!$this->confirm("Did you mean '{$extension->extension()->getName()}'?", false)) {
                    return null;
                }
            } else {
                return null;
            }
        }
        return $extension;
    }

    public function checkStatus($extension, $enabled): bool
    {
        $status = $enabled ? 'enabled' : 'disabled';
        if ($extension->status === $status) {
            $this->error("Extension '{$extension->extension()->getName()}' is already {$status}.");
            return false;
        }
        return true;
    }

    public function logAction($action, $extension): void
    {
        if (is_string($extension)) {
            logs()->notice("{$action} extension '{$extension}'.");
        } else {
            logs()->notice("{$action} extension '{$extension->extension()->getId()}'.");
        }
    }


    public function executeExtensionCommand($extension, $enabled, $action): void
    {
        if (!$this->checkStatus($extension, $enabled)) return;
        $this->info("{$action} '{$extension->extension()->getName()}'...");
        $enabled ? $extension->enable() : $extension->disable();
        $this->info("'{$extension->extension()->getName()}' {$action}.");
        $this->logAction($action, $extension);
    }
}
