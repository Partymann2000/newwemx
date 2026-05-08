<?php

use App\Actions\GatewayActions;
use Illuminate\Database\Migrations\Migration;
use App\Actions\ServerConnectionActions;
use App\Models\Extension;

return new class extends Migration {

    // list of extensions that should be enabled by default
    protected $defaultEnabledExtensions = [
        'server-universal',
        'gateway-balance',
        'gateway-sandbox',
        'gateway-sandbox-subscription',
    ];

    public function up()
    {
        // Discover and register all extensions to ensure default data is seeded for new extensions
        Extension::discover();

        // go through all default enabled extensions and enable them if they are installed
        foreach ($this->defaultEnabledExtensions as $extensionIdentifier) {
            $extension = Extension::find($extensionIdentifier);

            if ($extension && $extension->isDisabled()) {
                $extension->enable();
            }
        }

        // Create a universal server connection
        ServerConnectionActions::createServerConnectionAsAdmin([
            'alias' => 'Universal Server',
            'extension_identifier' => 'server-universal',
            'status' => 'healthy',
        ]);

        // Create a balance gateway config
        GatewayActions::createGatewayConfigAsAdmin([
            'extension_identifier' => 'gateway-balance',
            'display_name' => 'Balance Gateway',
            'display_description' => 'Pay with your account balance.',
            'config' => [],
            'is_enabled' => true,
            'webhook_id' => null,
        ]);

        // create sandbox gateway that is admin only
        GatewayActions::createGatewayConfigAsAdmin([
            'extension_identifier' => 'gateway-sandbox',
            'display_name' => 'Sandbox Gateway',
            'display_description' => 'Sandbox payment gateway for testing payments.',
            'config' => [],
            'is_enabled' => true,
            'is_staff_only' => true,
            'webhook_id' => null,
        ]);

        // create sandbox subscription gateway that is admin only
        GatewayActions::createGatewayConfigAsAdmin([
            'extension_identifier' => 'gateway-sandbox-subscription',
            'display_name' => 'Sandbox Subscription Gateway',
            'display_description' => 'Sandbox subscription gateway for testing recurring payments.',
            'config' => [],
            'is_enabled' => true,
            'is_staff_only' => true,
            'webhook_id' => null,
        ]);
    }

    public function down()
    {
    }
};
