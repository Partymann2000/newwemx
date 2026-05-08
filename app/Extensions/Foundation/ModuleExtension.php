<?php

namespace App\Extensions\Foundation;

abstract class ModuleExtension extends ExtensionFoundation
{
    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     *
     * @var string
     */
    protected string $type = 'Module';

    public function __construct()
    {
        parent::__construct();
    }
    abstract function onInstall(): void;

    abstract function onUninstall(): void;

    abstract function onEnable(): void;

    abstract function onDisable(): void;
}
