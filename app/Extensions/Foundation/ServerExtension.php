<?php

namespace App\Extensions\Foundation;

use App\Extensions\Traits\ServerHelper;
use App\Models\ServerConnection;
use App\Models\Package;
use App\Models\Order;

abstract class ServerExtension extends ExtensionFoundation
{
    use ServerHelper;

    protected string $type = 'Server';

    public function __construct()
    {
        parent::__construct();
    }

    abstract public function setConfig(): array;

    abstract public function setPackageConfig(Package $package, ServerConnection $connection): array;

    abstract function create(Order $order, ServerConnection $connection);

    abstract function suspend(Order $order, ServerConnection $connection);

    abstract function unsuspend(Order $order, ServerConnection $connection);

    abstract function terminate(Order $order, ServerConnection $connection);
}
