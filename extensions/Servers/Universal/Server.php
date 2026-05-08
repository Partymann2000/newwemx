<?php

namespace Extensions\Servers\Universal;

use App\Extensions\Foundation\ServerExtension;
use App\Models\ServerConnection;
use Illuminate\Support\Facades\Http;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Order;
use Illuminate\Support\Str;
use Exception;

class Server extends ServerExtension
{
    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "module-example".
     *
     * @var string
     */
    protected string $id = 'server-universal';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'Universal Server Module';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'The Universal Server Module can be used to create orders for anything.';

    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     *
     * @var string
     */
    protected string $type = 'Server';

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
    protected array $wemxVersions = ['1.0.0'];

    /**
     * Define the authors of the extension.
     *
     * @var array
     */
    protected array $authors = [
        [
            'name' => 'Mubeen',
            'email' => 'mubeen@wemx.net',
        ]
    ];

    /**
     * List of providers to be registered.
     */
    public function providers(): array
    {
        return [];
    }

    public function elements(): array
    {
        return [];
    }

    public function setSettingsFields(): array
    {
        return [];
    }

    public function __construct(
        protected ?ServerConnection $connection = null,
        protected ?Order $order = null,
    )
    {
        parent::__construct();
    }

    public function setConfig(): array
    {
        return [];
    }

    public function setPackageConfig(Package $package, ServerConnection $connection): array
    {
        return [];
    }

    public function setCheckoutConfig(Package $package): array
    {
        return [];
    }

    /**
     * This function is responsible for creating an instance of the
     * service. This can be anything such as a server, vps or any other instance.
     *
     * @return void
     */
    public function create(Order $order, ServerConnection $connection)
    {
        // not needed for universal server module
    }

    /**
     * This function is responsible for suspending an instance of the
     * service. This method is called when an order is expired or
     * suspended by an admin
     *
     * @param Order $order
     * @param ServerConnection $connection
     * @return void
     */
    public function suspend(Order $order, ServerConnection $connection)
    {
        // throw new Exception('Suspending is not supported for the Universal Server Module.');
    }

    /**
     * This function is responsible for unsuspending an instance of the
     * service. This method is called when an order is activated or
     * unsuspended by an admin
     *
     * @param Order $order
     * @param ServerConnection $connection
     * @return void
     */
    public function unsuspend(Order $order, ServerConnection $connection)
    {
        // not needed for universal server module
    }

    /**
     * This function is responsible for deleting an instance of the
     * service. This can be anything such as a server, vps or any other instance.
     *
     * @param Order $order
     * @param ServerConnection $connection
     * @return void
     */
    public function terminate(Order $order, ServerConnection $connection)
    {
        // not needed for universal server module
    }

    public function upgradeOrDowngrade(Order $order, PackagePrice $oldPackagePrice, PackagePrice $newPackagePrice, ServerConnection $connection)
    {
        // do something
    }
}
