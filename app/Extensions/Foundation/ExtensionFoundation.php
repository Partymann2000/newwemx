<?php

namespace App\Extensions\Foundation;

use App\Extensions\Traits\ExtensionHelper;

abstract class ExtensionFoundation
{
    use ExtensionHelper;

    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "example-module".
     *
     * @var string
     */
    protected string $id;

    /**
     * Define the extension display namme
     *
     * @var string
     */
    protected string $name;

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'No description provided.';

    /**
     * Define the extension icon.
     *
     * @var string
     */
    protected string $icon = '/assets/img/extensions/default.png';

    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     *
     * @var string
     */
    protected string $type;

    /**
     * Define the ID of this extension in the marketplace.
     *
     * @var string
     */
    protected string $marketplace_id;

    /**
     * Define the extension version.
     *
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Define the WemX versions that the extension is compatible with.
     * Use * to define that the extension is compatible with all versions.
     *
     * @var array
     */
    protected array $wemxVersions = [];

    /**
     * Define the authors of the extension.
     *
     * @var array
     */
    protected array $authors = [];

    public function __construct()
    {

    }
}
