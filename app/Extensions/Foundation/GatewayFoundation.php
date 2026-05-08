<?php

namespace App\Extensions\Foundation;

use App\Extensions\Traits\GatewayFoundationHelper;

abstract class GatewayFoundation extends ExtensionFoundation
{
    use GatewayFoundationHelper;

    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     *
     * @var string
     */
    protected string $type = 'Gateway';

    /**
     * Define the gateway type. Can be "subscription" or "payment".
     *
     * @var string
     */
    protected string $gatewayType = 'payment';

    /**
     * Define the supported currencies.
     *
     * @var array
     */
    protected array $currencies = [];

    public function __construct()
    {
        parent::__construct();
    }

    abstract public static function setConfig(): array;
}
