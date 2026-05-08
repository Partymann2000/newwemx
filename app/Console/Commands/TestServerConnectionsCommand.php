<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Email;
use App\Models\Package;
use Illuminate\Console\Command;
use App\Models\ServerConnection;

class TestServerConnectionsCommand extends Command
{
    protected $signature = 'server-connections:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->info("Testing server connections...");

        $connections = ServerConnection::where('is_active', true)->get();
        foreach ($connections as $connection) {
            try {
                $this->info("Testing connection to {$connection->alias}...");

                // check if testConnection method exists
                if(!$connection->server->hasTestConnection()) {
                    $this->info("Skipping {$connection->alias} as testConnection method does not exist");
                    continue;
                }

                $status = $connection->server->testConnection($connection->config ?? []);

                if($status === false) {
                    throw new \Exception("Server returned a 'false' response status");
                }

                // if connection status is unavailable, send an email to the user
                if($connection->status === 'unavailable') {
                    $email = Email::actions()->sendEmailToAddress([
                        'identifier' => 'server-connection-online-'. $connection->id,
                        'to' => $connection->alert_email,
                        'subject' => "Connection to server {$connection->alias} is back online",
                        'lines' => [
                            "You are receiving this email because you enabled connection alerts for server {$connection->alias}.",
                            "Connection to server {$connection->alias} appears to be back online."
                        ],
                        'button_text' => 'View server',
                        'button_url' => route('admin.servers.connections.edit', $connection->id),
                    ]);
                }

                $connection->update([
                    'status' => 'healthy',
                    'last_checked_at' => now(),
                ]);

                $this->info("Connection to {$connection->alias} successful");

            } catch (\Exception $e) {
                if($connection->receive_alerts AND $connection->alert_email) {
                    $emailData = [
                        'identifier' => 'server-connection-failed-'. $connection->id,
                        'to' => $connection->alert_email,
                        'subject' => "Connection to server {$connection->alias} is down",
                        'lines' => [
                            "You are receiving this email because you enabled connection alerts for server {$connection->alias}.",
                            "Connection to server {$connection->alias} failed: ",
                            "```{$e->getMessage()}```"
                        ],
                        'button_text' => 'View server',
                        'button_url' => route('admin.servers.connections.edit', $connection->id),
                    ];

                    // if current status is not healthy, we add cooldown of 60 minutes
                    //dd($connection->status);
                    if($connection->status != 'healthy') {
                        $emailData['cooldown'] = 180;
                    }

                    $email = Email::actions()->sendEmailToAddress($emailData);
                }

                $connection->update([
                    'status' => 'unavailable',
                    'last_checked_at' => now(),
                ]);

                // remove all packages from carts that use this server connection
                if ($connection->prevent_purchasing) {
                    // get all packages using this server connection
                    $packages = Package::where('connection_id', $connection->id)->get();

                    // get all package price ids
                    $packagePriceIds = [];

                    foreach($packages as $package) {
                        $prices = $package->prices;
                        foreach($prices as $price) {
                            $packagePriceIds[] = $price->id;
                        }
                    }

                    // remove these package prices from all carts
                    if(count($packagePriceIds) > 0) {
                        CartItem::where('cartable_type', 'App\Models\PackagePrice')
                            ->whereIn('cartable_id', $packagePriceIds)
                            ->get()
                            ->each(function($item) {
                                $item->remove();
                            });
                    }
                }

                $this->error("Connection {$connection->alias} failed: {$e->getMessage()}");
            }
        }
    }
}
