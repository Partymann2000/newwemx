<?php

namespace App\Console\Commands;

use App\Actions\CategoryActions;
use App\Actions\PackageActions;
use App\Actions\PaymentActions;
use App\Facades\World;
use App\Handlers\CartCompletedHandler;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\GatewayConfig;
use App\Models\Order;
use App\Models\OrderSubscription;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Payment;
use App\Models\ServerConnection;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ApplicationTestDataCommand extends Command
{
    private const MAX_SIMULATED_PAYMENT_AMOUNT = 300.00;

    protected $signature = 'application:test-data
        {--users=500 : Number of fake users to create with addresses}
        {--orders=1200 : Number of checkout orders to create in the last 2 years}
        {--payments=1800 : Number of paid payments to create in the last 2 years}
        {--subscriptions=450 : Number of order subscriptions to create in the last 2 years}
        {--force : Run without confirmation (for scripts and non-interactive use)}';

    protected $description = 'Seeds the application with test data';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('The application:test-data command cannot be run in production.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->newLine();
            $this->warn('This command will seed fake test data into your database.');
            $this->warn('The operation is irreversible without restoring from a backup or manually removing the seeded records.');
            $this->newLine();

            if (! $this->confirm('Do you want to continue?')) {
                $this->info('Aborted.');

                return self::FAILURE;
            }

            $this->newLine();
        }

        Setting::put('enable_sales_tax', '1');
        $this->info('Sales tax collection enabled in application settings.');

        if (User::query()->first() === null) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
            ]);
        }

        $this->seedUniversalServiceCatalog();

        $users = $this->seedFakeUsers(max(0, (int) $this->option('users')));

        $this->seedCommerceActivity(
            $users,
            max(0, (int) $this->option('orders')),
            max(0, (int) $this->option('payments')),
            max(0, (int) $this->option('subscriptions'))
        );

        $this->info('Test data seeded successfully.');

        return self::SUCCESS;
    }

    /**
     * Seed categories and packages on the Universal server module with realistic tiers, features, and billing.
     */
    private function seedUniversalServiceCatalog(): void
    {
        $connection = ServerConnection::query()
            ->where('extension_identifier', 'server-universal')
            ->where('is_active', true)
            ->first()
            ?? ServerConnection::query()
                ->where('extension_identifier', 'server-universal')
                ->first();

        if (! $connection) {
            throw new RuntimeException(
                'No Universal Server connection found (extension_identifier server-universal). Enable the extension and run migrations that create default connections.'
            );
        }

        $catalog = [
            [
                'kind' => 'web_hosting',
                'name' => 'Web Hosting',
                'description' => 'Shared hosting for websites and small applications. SSD storage, free SSL, and one-click installs.',
                'plans' => [
                    ['label' => 'Starter', 'monthly' => 3.99, 'features' => ['1 website', '10 GB NVMe SSD', 'Unmetered bandwidth', 'Free Let\'s Encrypt SSL', 'Weekly backups', 'Email support (48h)', '99.9% uptime SLA']],
                    ['label' => 'Personal', 'monthly' => 6.99, 'features' => ['3 websites', '25 GB NVMe SSD', 'Unmetered bandwidth', 'Free SSL (wildcard-ready)', 'Daily backups', '5 email accounts', 'Priority email support']],
                    ['label' => 'Professional', 'monthly' => 11.99, 'features' => ['10 websites', '50 GB NVMe SSD', 'Unmetered bandwidth', 'Free SSL + staging URL', 'Daily backups + on-demand', '25 email accounts', 'Live chat support']],
                    ['label' => 'Business', 'monthly' => 18.99, 'features' => ['Unlimited websites', '100 GB NVMe SSD', 'Unmetered bandwidth', 'Free SSL + malware scan', 'Daily + off-site backups', 'Unlimited email', 'Live chat + phone queue']],
                    ['label' => 'Premium', 'monthly' => 29.99, 'features' => ['Unlimited websites', '200 GB NVMe SSD', 'Global CDN included', 'Advanced WAF rules', 'Hourly backups', 'Unlimited email + archive', '24/7 priority support']],
                    ['label' => 'Elite', 'monthly' => 45.99, 'features' => ['Unlimited websites', '400 GB NVMe SSD', 'CDN + edge caching', 'Managed WordPress toolkit', 'Real-time replication', 'Dedicated IP included', 'Named technical account manager']],
                    ['label' => 'Enterprise', 'monthly' => 79.99, 'features' => ['Unlimited websites', '800 GB NVMe SSD', 'Premium CDN + DDoS mitigation', 'Compliance-ready backups', 'Custom integration window', 'White-glove migration', '24/7 phone + chat SLA']],
                ],
            ],
            [
                'kind' => 'vps',
                'name' => 'Cloud VPS',
                'description' => 'Linux virtual machines with dedicated resources. Full root access, snapshots, and predictable monthly pricing.',
                'plans' => [
                    ['label' => 'Nano', 'monthly' => 7.99, 'features' => ['1 vCPU (shared burstable)', '1 GB RAM', '25 GB NVMe', '1 TB transfer', '1 IPv4 address', 'Snapshot (1 slot)', 'Community documentation']],
                    ['label' => 'Micro', 'monthly' => 12.99, 'features' => ['1 vCPU (dedicated)', '2 GB RAM', '50 GB NVMe', '2 TB transfer', '1 IPv4 address', 'Snapshots (2 slots)', 'Email support (business hours)']],
                    ['label' => 'Small', 'monthly' => 24.99, 'features' => ['2 vCPU', '4 GB RAM', '80 GB NVMe', '3 TB transfer', '1 IPv4 + IPv6 /64', 'Snapshots (3 slots)', 'Monitoring alerts (email)']],
                    ['label' => 'Medium', 'monthly' => 47.99, 'features' => ['4 vCPU', '8 GB RAM', '160 GB NVMe', '4 TB transfer', '1 IPv4 + IPv6 /64', 'Snapshots (5 slots)', 'Live chat support']],
                    ['label' => 'Large', 'monthly' => 94.99, 'features' => ['8 vCPU', '16 GB RAM', '320 GB NVMe', '6 TB transfer', '1 IPv4 + IPv6 /64', 'Snapshots (7 slots)', 'Priority support + runbooks']],
                    ['label' => 'XL', 'monthly' => 189.99, 'features' => ['16 vCPU', '32 GB RAM', '640 GB NVMe', '10 TB transfer', '2× IPv4 + IPv6 /64', 'Snapshots (10 slots)', '24/7 priority support']],
                ],
            ],
            [
                'kind' => 'email',
                'name' => 'Email & Productivity',
                'description' => 'Hosted mailboxes with spam filtering, calendars, and shared contacts for teams.',
                'plans' => [
                    ['label' => 'Essentials', 'monthly' => 1.99, 'features' => ['1 mailbox (25 GB)', 'Webmail + IMAP/SMTP', 'Spam & virus filtering', '10 mailing lists', 'Mobile sync (ActiveSync)', 'Standard support']],
                    ['label' => 'Team', 'monthly' => 7.99, 'features' => ['5 mailboxes (25 GB each)', 'Shared calendars & contacts', 'Advanced anti-phishing', '50 mailing lists', 'Optional archive add-on', 'Business-hours support']],
                    ['label' => 'Business', 'monthly' => 24.99, 'features' => ['25 mailboxes (50 GB each)', 'Legal hold & audit export', 'Per-mailbox retention policies', 'Unlimited aliases', 'SSO-ready directory sync', 'Priority support + onboarding']],
                ],
            ],
            [
                'kind' => 'game',
                'name' => 'Game Servers',
                'description' => 'Low-latency game hosting with DDoS protection and quick slot scaling for community servers.',
                'plans' => [
                    ['label' => 'Bronze', 'monthly' => 14.99, 'features' => ['Up to 10 player slots', '6 GB RAM', '30 GB NVMe', 'Basic DDoS protection', 'FTP + file manager', 'Automated restarts', 'Discord status webhook']],
                    ['label' => 'Silver', 'monthly' => 32.99, 'features' => ['Up to 24 player slots', '12 GB RAM', '60 GB NVMe', 'Advanced DDoS mitigation', 'Scheduled backups (daily)', 'Mod installer', 'Priority ticket queue']],
                    ['label' => 'Gold', 'monthly' => 59.99, 'features' => ['Up to 48 player slots', '24 GB RAM', '120 GB NVMe', 'Premium DDoS + scrubbing', 'Hourly backups + restore', 'Custom launch flags', '24/7 game-specific support']],
                ],
            ],
        ];

        $this->newLine();
        $this->info('Seeding Universal service catalog (categories & plans)...');

        foreach ($catalog as $category) {
            $categoryModel = CategoryActions::createCategoryAsAdmin([
                'name' => $category['name'],
                'description' => $category['description'],
                'slug' => Str::slug($category['name']).'-'.Str::lower(Str::random(8)),
                'status' => 'active',
            ]);

            foreach ($category['plans'] as $plan) {
                $packageDisplayName = $category['name'].' — '.$plan['label'].' · '.Str::lower(Str::random(6));
                $marketing = $this->planMarketingCopy($category['kind'], $plan['label'], (float) $plan['monthly']);

                $package = PackageActions::createPackageAsAdmin([
                    'name' => $packageDisplayName,
                    'slug' => Str::slug($packageDisplayName).'-'.Str::lower(Str::random(6)),
                    'category_id' => $categoryModel->id,
                    'connection_id' => $connection->id,
                    'status' => 'active',
                    'short_description' => $marketing['short_description'],
                    'description' => $marketing['description'],
                ]);

                foreach ($plan['features'] as $description) {
                    PackageActions::createFeatureAsAdmin([
                        'package_id' => $package->id,
                        'description' => $description,
                    ]);
                }

                $this->applyLogicalRecurringPrices($package, (float) $plan['monthly']);
                $this->seedPackageConfigOptions($package, $category['kind']);
            }
        }

        $this->newLine();
    }

    private function seedPackageConfigOptions(Package $package, string $kind): void
    {
        match ($kind) {
            'web_hosting' => $this->seedWebHostingConfigOptions($package),
            'vps' => $this->seedVpsConfigOptions($package),
            'game' => $this->seedGameServerConfigOptions($package),
            'email' => $this->seedEmailConfigOptions($package),
            default => null,
        };
    }

    private function createPackageConfigOption(Package $package, array $payload): void
    {
        PackageActions::createConfigOptionAsAdmin(array_merge(
            ['package_id' => $package->id],
            $payload
        ));
    }

    private function seedWebHostingConfigOptions(Package $package): void
    {
        $this->createPackageConfigOption($package, [
            'key' => 'hosting_region',
            'label' => 'Server location',
            'description' => 'Choose where your site is served from. Standard regions are included; premium regions add a small monthly surcharge.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => 'us-east',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => 'us-east', 'name' => 'US East (Virginia) — included', 'daily_price' => 0],
                    ['value' => 'eu-central', 'name' => 'EU Central (Frankfurt) — included', 'daily_price' => 0],
                    ['value' => 'uk-london', 'name' => 'UK (London) — included', 'daily_price' => 0],
                    ['value' => 'ap-sg', 'name' => 'Asia Pacific (Singapore) — +$3.00/mo', 'daily_price' => round(3 / 30, 4)],
                    ['value' => 'ap-tokyo', 'name' => 'Asia Pacific (Tokyo) — +$4.50/mo', 'daily_price' => round(4.5 / 30, 4)],
                    ['value' => 'oc-sydney', 'name' => 'Oceania (Sydney) — +$5.00/mo', 'daily_price' => round(5 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'primary_domain',
            'label' => 'Primary domain',
            'description' => 'The main domain you want to use for this hosting account (e.g. example.com).',
            'type' => 'text',
            'rules' => 'required|string|max:253',
            'default_value' => 'example.com',
            'onetime_day_equivalent' => 30,
            'data' => [],
        ]);
    }

    private function seedVpsConfigOptions(Package $package): void
    {
        $this->createPackageConfigOption($package, [
            'key' => 'vps_region',
            'label' => 'Data center location',
            'description' => 'Pick a region close to your users. Core regions are included; premium regions add a daily surcharge.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => 'us-east',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => 'us-east', 'name' => 'US East — included', 'daily_price' => 0],
                    ['value' => 'eu-west', 'name' => 'EU West — included', 'daily_price' => 0],
                    ['value' => 'ca-central', 'name' => 'Canada Central — +$2.00/mo', 'daily_price' => round(2 / 30, 4)],
                    ['value' => 'ap-east', 'name' => 'Asia East — +$4.00/mo', 'daily_price' => round(4 / 30, 4)],
                    ['value' => 'me-dubai', 'name' => 'Middle East — +$6.00/mo', 'daily_price' => round(6 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'extra_ram_gb',
            'label' => 'Additional RAM (GB)',
            'description' => 'Your plan includes a base allocation; use the slider to add more RAM. The first 0 GB add-on is free.',
            'type' => 'range',
            'rules' => 'required|integer|min:0|max:64',
            'default_value' => '0',
            'onetime_day_equivalent' => 30,
            'data' => [
                'min_value' => 0,
                'max_value' => 64,
                'step_value' => 2,
                'free_value' => 0,
                'daily_price' => 0.12,
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'extra_storage_gb',
            'label' => 'Additional NVMe storage (GB)',
            'description' => 'Add disk space in 25 GB steps. Included amount is covered at no extra charge.',
            'type' => 'range',
            'rules' => 'required|integer|min:0|max:1000',
            'default_value' => '0',
            'onetime_day_equivalent' => 30,
            'data' => [
                'min_value' => 0,
                'max_value' => 1000,
                'step_value' => 25,
                'free_value' => 0,
                'daily_price' => 0.04,
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'server_username',
            'label' => 'Server username',
            'description' => 'Linux username for SSH (letters, numbers, underscore; must start with a letter).',
            'type' => 'text',
            'rules' => 'required|string|min:3|max:32|regex:/^[a-z][a-z0-9_]*$/i',
            'default_value' => 'root',
            'onetime_day_equivalent' => 30,
            'data' => [],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'server_password',
            'label' => 'Root / sudo password',
            'description' => 'Choose a strong password for the default superuser account (minimum 12 characters).',
            'type' => 'password',
            'rules' => 'required|string|min:12|max:128',
            'default_value' => Str::password(16),
            'onetime_day_equivalent' => 30,
            'data' => [],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'operating_system',
            'label' => 'Operating system',
            'description' => 'Select the OS image provisioned on first boot. All options are included in the base price.',
            'type' => 'radio',
            'rules' => 'required|string',
            'default_value' => 'ubuntu-2404',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    [
                        'value' => 'ubuntu-2404',
                        'name' => 'Ubuntu 24.04 LTS',
                        'description' => 'Long-term support, broad package ecosystem.',
                        'icon_url' => 'https://cdn.simpleicons.org/ubuntu/E95420',
                        'daily_price' => 0,
                    ],
                    [
                        'value' => 'debian-12',
                        'name' => 'Debian 12',
                        'description' => 'Stable, minimal base — great for production servers.',
                        'icon_url' => 'https://cdn.simpleicons.org/debian/A81D84',
                        'daily_price' => 0,
                    ],
                    [
                        'value' => 'almalinux-9',
                        'name' => 'AlmaLinux 9',
                        'description' => 'RHEL-compatible enterprise Linux.',
                        'icon_url' => 'https://cdn.simpleicons.org/almalinux/000000',
                        'daily_price' => 0,
                    ],
                    [
                        'value' => 'windows-2022',
                        'name' => 'Windows Server 2022',
                        'description' => 'Bring your own license model; includes Desktop Experience option.',
                        'icon_url' => 'https://cdn.simpleicons.org/windows/0078D6',
                        'daily_price' => 0,
                    ],
                ],
            ],
        ]);
    }

    private function seedGameServerConfigOptions(Package $package): void
    {
        $this->createPackageConfigOption($package, [
            'key' => 'game_region',
            'label' => 'Server location',
            'description' => 'Lower ping for players near the selected region. Some regions include a small surcharge.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => 'us-central',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => 'us-central', 'name' => 'US Central (Chicago) — included', 'daily_price' => 0],
                    ['value' => 'us-west', 'name' => 'US West (Los Angeles) — included', 'daily_price' => 0],
                    ['value' => 'eu-north', 'name' => 'EU North (Stockholm) — included', 'daily_price' => 0],
                    ['value' => 'apac', 'name' => 'Asia Pacific (Tokyo) — +$5.00/mo', 'daily_price' => round(5 / 30, 4)],
                    ['value' => 'oceania', 'name' => 'Oceania (Sydney) — +$6.50/mo', 'daily_price' => round(6.5 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'game_ram_profile',
            'label' => 'Allocated RAM',
            'description' => 'Higher RAM improves modpacks and player count headroom.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => '8gb',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => '6gb', 'name' => '6 GB — included in base', 'daily_price' => 0],
                    ['value' => '8gb', 'name' => '8 GB — +$4.00/mo', 'daily_price' => round(4 / 30, 4)],
                    ['value' => '12gb', 'name' => '12 GB — +$10.00/mo', 'daily_price' => round(10 / 30, 4)],
                    ['value' => '16gb', 'name' => '16 GB — +$18.00/mo', 'daily_price' => round(18 / 30, 4)],
                    ['value' => '24gb', 'name' => '24 GB — +$32.00/mo', 'daily_price' => round(32 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'game_storage_profile',
            'label' => 'World & mod storage',
            'description' => 'Extra NVMe space for worlds, mods, and backups.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => '40gb',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => '30gb', 'name' => '30 GB — included', 'daily_price' => 0],
                    ['value' => '40gb', 'name' => '40 GB — +$2.00/mo', 'daily_price' => round(2 / 30, 4)],
                    ['value' => '80gb', 'name' => '80 GB — +$6.00/mo', 'daily_price' => round(6 / 30, 4)],
                    ['value' => '160gb', 'name' => '160 GB — +$12.00/mo', 'daily_price' => round(12 / 30, 4)],
                    ['value' => '320gb', 'name' => '320 GB — +$22.00/mo', 'daily_price' => round(22 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'server_display_name',
            'label' => 'Server list name',
            'description' => 'Shown in the public server browser and panel (e.g. My SMP Season 3).',
            'type' => 'text',
            'rules' => 'required|string|min:3|max:64',
            'default_value' => 'My Game Server',
            'onetime_day_equivalent' => 30,
            'data' => [],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'game_tickrate',
            'label' => 'Simulation tick rate',
            'description' => 'Higher tick rates reduce lag spikes on busy servers (small monthly add-on).',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => '20',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => '20', 'name' => '20 TPS — standard (included)', 'daily_price' => 0],
                    ['value' => '30', 'name' => '30 TPS — +$3.00/mo', 'daily_price' => round(3 / 30, 4)],
                    ['value' => '60', 'name' => '60 TPS — competitive — +$8.00/mo', 'daily_price' => round(8 / 30, 4)],
                ],
            ],
        ]);

        $this->createPackageConfigOption($package, [
            'key' => 'automatic_backups',
            'label' => 'Automatic world backups',
            'description' => 'Scheduled snapshots to off-site storage for faster restores.',
            'type' => 'select',
            'rules' => 'required|string',
            'default_value' => 'none',
            'onetime_day_equivalent' => 30,
            'data' => [
                'options' => [
                    ['value' => 'none', 'name' => 'Manual only — included', 'daily_price' => 0],
                    ['value' => 'daily', 'name' => 'Daily retained 7 days — +$4.00/mo', 'daily_price' => round(4 / 30, 4)],
                    ['value' => 'hourly', 'name' => 'Hourly retained 48h + daily 14d — +$9.00/mo', 'daily_price' => round(9 / 30, 4)],
                ],
            ],
        ]);
    }

    private function seedEmailConfigOptions(Package $package): void
    {
        $this->createPackageConfigOption($package, [
            'key' => 'mail_primary_domain',
            'label' => 'Primary mail domain',
            'description' => 'The domain you will send and receive mail on (we will provide DNS records after checkout).',
            'type' => 'text',
            'rules' => 'required|string|max:253',
            'default_value' => 'example.com',
            'onetime_day_equivalent' => 30,
            'data' => [],
        ]);
    }

    /**
     * Updates the default monthly row from package creation and adds quarterly (7% off 3× monthly) and yearly (18% off 12× monthly).
     */
    private function applyLogicalRecurringPrices(Package $package, float $monthly): void
    {
        $monthlyRow = $package->prices()
            ->where('period_in_days', 30)
            ->orderBy('id')
            ->first();

        if ($monthlyRow) {
            PackageActions::updatePackagePriceAsAdmin([
                'price_id' => $monthlyRow->id,
                'short_description' => 'Monthly',
                'period_in_days' => 30,
                'price' => round($monthly, 2),
            ]);
        }

        $quarterly = round($monthly * 3 * 0.93, 2);
        $yearly = round($monthly * 12 * 0.82, 2);

        PackageActions::createPackagePriceAsAdmin([
            'package_id' => $package->id,
            'short_description' => 'Quarterly (save vs. monthly)',
            'period_in_days' => 90,
            'price' => $quarterly,
        ]);

        PackageActions::createPackagePriceAsAdmin([
            'package_id' => $package->id,
            'short_description' => 'Yearly (best value)',
            'period_in_days' => 365,
            'price' => $yearly,
        ]);
    }

    /**
     * @return array{short_description: string, description: string}
     */
    private function planMarketingCopy(string $kind, string $label, float $monthly): array
    {
        $p = number_format($monthly, 2);

        return match ($kind.'|'.$label) {
            'web_hosting|Starter' => [
                'short_description' => 'Single-site NVMe hosting with free SSL — publish your first site quickly.',
                'description' => "## Starter hosting\n\nLaunch on **shared NVMe** with **free Let's Encrypt SSL**, weekly backups, and clear upgrade paths as you grow.\n\n### Ideal for\n\n- Personal blogs and portfolios  \n- Brochure sites and lightweight **WordPress** installs  \n- Teams validating traffic before scaling tiers  \n\n**From \${$p}/mo** on the monthly billing cycle (quarterly & yearly show automatic savings in the summary).\n\n> Choose a region and primary domain at checkout — premium regions add a small surcharge you will see itemized instantly.",
            ],
            'web_hosting|Personal' => [
                'short_description' => 'Up to three sites, daily backups, and mailboxes for side projects.',
                'description' => "## Personal\n\nRun **up to three production sites** on faster quotas with **daily backups** and bundled mailboxes for real projects.\n\n### Highlights\n\n- NVMe-backed storage with unmetered bandwidth on fair-use policy  \n- **Wildcard-ready SSL** for subdomains and staging hosts  \n- Priority email support when you need a human  \n\n**From \${$p}/mo** monthly before configurable add-ons.\n\n### Markdown extras\n\n| Topic | Detail |\n| --- | --- |\n| Backups | Daily snapshots + restore from client area |\n| Email | Five active mailboxes with spam filtering |\n\n> Upgrade to **Professional** when you need staging workflows and live chat.",
            ],
            'web_hosting|Professional' => [
                'short_description' => 'Ten sites, staging URLs, and live chat for growing agencies.',
                'description' => "## Professional hosting\n\nBuilt for **studios and SaaS landing fleets**: host ten properties, ship staging URLs, and lean on **live chat** when deadlines hit.\n\n### What you get\n\n1. **50 GB NVMe** with predictable performance monitoring  \n2. On-demand backups layered on top of daily jobs  \n3. Twenty-five mailboxes with advanced routing rules  \n\n**From \${$p}/mo** on monthly billing.\n\n```\nTip: pair this tier with paid regions for global campaigns.\n```\n\n> Includes malware-aware SSL issuance and renewal reminders.",
            ],
            'web_hosting|Business' => [
                'short_description' => 'Unlimited sites, malware-aware SSL, and phone-queue escalation.',
                'description' => "## Business\n\nScale **unlimited brands** on one invoice with **malware scanning**, off-site backups, and **live chat + phone queue** coverage.\n\n### Reliability stack\n\n- **100 GB NVMe** with burst-friendly networking  \n- Daily + off-site backups for compliance-friendly retention  \n- Unlimited mailboxes with archiving hooks  \n\n**From \${$p}/mo** monthly.\n\n> Quote block: *“We designed this tier for teams that cannot afford silent failures overnight.”*\n\nConfigurable checkout captures region surcharges transparently.",
            ],
            'web_hosting|Premium' => [
                'short_description' => 'Global CDN, advanced WAF, and hourly backups for mission traffic.',
                'description' => "## Premium\n\nServe worldwide audiences through our **global CDN**, protect surfaces with **advanced WAF** policies, and rely on **hourly backups**.\n\n### Performance & security\n\n- **200 GB NVMe** with edge caching profiles tuned for dynamic CMS workloads  \n- Unlimited mailboxes plus optional legal-hold style exports (partner add-on)  \n- **24/7 priority** routing for sev-1 incidents  \n\n**From \${$p}/mo** on monthly cycles.\n\n### Checklist\n\n- [x] CDN included  \n- [x] Hourly backups  \n- [x] Priority support  \n\n> Upgrade path to **Elite** unlocks dedicated IP and named TAM coverage.",
            ],
            'web_hosting|Elite' => [
                'short_description' => 'Dedicated IP, replication, and a named technical account manager.',
                'description' => "## Elite\n\n**Dedicated IPv4**, real-time replication pairs, and a **named TAM** who understands your architecture.\n\n### Platform depth\n\n- **400 GB NVMe** with CDN + edge caching profiles  \n- Managed WordPress toolkit (themes, staging, safe updates)  \n- Snapshot orchestration API for CI-driven refreshes  \n\n**From \${$p}/mo** monthly before optional region fees.\n\n> *Perfect when compliance stakeholders want named contacts and predictable change windows.*",
            ],
            'web_hosting|Enterprise' => [
                'short_description' => 'Compliance-ready backups, DDoS mitigation, and white-glove migrations.',
                'description' => "## Enterprise hosting\n\nMaximum **NVMe headroom**, **premium DDoS mitigation**, and **white-glove migrations** with compliance-ready backup exports.\n\n### Why teams pick Enterprise\n\n1. **800 GB NVMe** with multi-region replication options  \n2. Custom integration windows coordinated with your change board  \n3. **24/7 phone + chat SLA** with executive escalation paths  \n\n**From \${$p}/mo** on monthly billing.\n\n| Capability | Included |\n| --- | ---: |\n| Compliance exports | Yes |\n| DDoS mitigation | Premium tier |\n| Migration concierge | Yes |\n\n> Quote: **“We pair Universal automation with human operators for cutovers that cannot miss.”**",
            ],
            'vps|Nano' => [
                'short_description' => 'Burstable 1 vCPU, 1 GB RAM — ideal for bots, VPN endpoints, or labs.',
                'description' => "## Nano VPS\n\n**Burstable vCPU** with **1 GB RAM** and **25 GB NVMe** for always-on utilities that do not need a full-sized instance.\n\n### Common uses\n\n- Private **DNS** or metrics collectors  \n- Low-traffic APIs and staging hooks  \n- Learning **Linux** with snapshots for rollback practice  \n\n**From \${$p}/mo** monthly.\n\n> Checkout captures region, extra RAM/storage sliders, credentials, and OS image — all priced before you add to cart.",
            ],
            'vps|Micro' => [
                'short_description' => 'Dedicated vCPU thread, 2 GB RAM — great for small databases or CI workers.',
                'description' => "## Micro VPS\n\nDedicated **vCPU thread**, **2 GB RAM**, and **50 GB NVMe** with dual snapshot slots for safer iteration.\n\n### What you can run\n\n- Small **PostgreSQL** or Redis nodes  \n- CI runners with ephemeral caches  \n- Docker hosts for internal dashboards  \n\n**From \${$p}/mo** monthly.\n\n```bash\n# Typical first boot\nssh root@your-ip\napt update && apt upgrade -y\n```\n\n> OS cards include **Ubuntu**, **Debian**, **AlmaLinux**, and **Windows Server** artwork for quick recognition.",
            ],
            'vps|Small' => [
                'short_description' => '2 vCPU / 4 GB — production-ready for SaaS APIs and modest traffic.',
                'description' => "## Small VPS\n\nBalanced **2 vCPU / 4 GB** footprint with **80 GB NVMe**, IPv6 `/64`, and monitoring email alerts baked in.\n\n### Reliability\n\n- **3 TB** transfer quota suited to steady SaaS APIs  \n- Three snapshot slots for blue/green style releases  \n- IPv4 + IPv6 dual-stack ready  \n\n**From \${$p}/mo** on monthly billing.\n\n> Add RAM or disk with sliders — only the incremental units above the included allowance are billed.",
            ],
            'vps|Medium' => [
                'short_description' => '4 vCPU / 8 GB — live-chat backed support for growing workloads.',
                'description' => "## Medium VPS\n\n**4 vCPU / 8 GB** with **160 GB NVMe** and **live chat** support when you need operators who can read your graphs.\n\n### Highlights\n\n- **4 TB** transfer for bursty marketing campaigns  \n- Five snapshot slots for multi-environment testing  \n- Optional paid regions for global latency tuning  \n\n**From \${$p}/mo** monthly.\n\n> Markdown **bold** paths remind you to rotate the generated root password after provisioning.",
            ],
            'vps|Large' => [
                'short_description' => '8 vCPU / 16 GB — databases, caches, and multi-service Docker hosts.',
                'description' => "## Large VPS\n\n**8 vCPU / 16 GB** with **320 GB NVMe** and **6 TB** transfer — the sweet spot for consolidated Docker stacks.\n\n### Architecture ideas\n\n1. Primary app + worker queues on the same host  \n2. Read replicas or cache layers colocated for low latency  \n3. Observability agents with local SSD headroom  \n\n**From \${$p}/mo** monthly.\n\n> Includes **seven snapshot slots** and priority support with runbook references.",
            ],
            'vps|XL' => [
                'short_description' => '16 vCPU / 32 GB — throughput-heavy analytics and game backends.',
                'description' => "## XL VPS\n\n**16 vCPU / 32 GB**, **640 GB NVMe**, **10 TB** transfer, and **dual IPv4** plus IPv6 for demanding east-west traffic.\n\n### When XL fits\n\n- Heavy **analytics** workers close to your warehouse data  \n- Large **game** session directors or modded community backends  \n- Video pipeline staging with parallel encodes  \n\n**From \${$p}/mo** monthly.\n\n> **24/7 priority** support and ten snapshot slots keep large teams unblocked.",
            ],
            'email|Essentials' => [
                'short_description' => 'One professional mailbox with spam filtering and mobile sync.',
                'description' => "## Essentials mailbox\n\n**One 25 GB mailbox** with **ActiveSync**, webmail, and aggressive **spam + virus** filtering.\n\n### Includes\n\n- Ten mailing lists for announcements  \n- Standard support SLAs for small teams  \n- DNS guidance after you enter your primary domain at checkout  \n\n**From \${$p}/mo** monthly.\n\n> Perfect when you need a credible **@yourbrand.com** address without IT overhead.",
            ],
            'email|Team' => [
                'short_description' => 'Five mailboxes with shared calendars and anti-phishing controls.',
                'description' => "## Team mail\n\n**Five 25 GB mailboxes** with **shared calendars**, contacts, and **anti-phishing** heuristics tuned for SMB inboxes.\n\n### Collaboration\n\n- Fifty mailing lists for department broadcasts  \n- Optional archive add-on (quoted separately)  \n- Business-hours support with predictable response targets  \n\n**From \${$p}/mo** on monthly billing.\n\n> Markdown table idea: keep distribution lists documented in your wiki and link them here for onboarding.",
            ],
            'email|Business' => [
                'short_description' => 'Twenty-five mailboxes with legal hold, retention, and SSO-ready sync.',
                'description' => "## Business mail\n\n**Twenty-five 50 GB mailboxes** with **legal hold**, per-mailbox retention, and **SSO-ready** directory sync patterns.\n\n### Governance\n\n1. Audit exports for compliance questionnaires  \n2. Unlimited aliases mapped to monitored shared inboxes  \n3. Priority onboarding with DNS + MX verification checklist  \n\n**From \${$p}/mo** monthly.\n\n> *Designed for regulated teams that still want Universal-style automation in billing.*",
            ],
            'game|Bronze' => [
                'short_description' => 'Ten-slot community server with DDoS protection and FTP access.',
                'description' => "## Bronze game server\n\nHost up to **ten concurrent players** on **6 GB RAM** and **30 GB NVMe** with baseline **DDoS protection** and **FTP** access.\n\n### Operator friendly\n\n- Automated restarts and Discord status webhooks  \n- Configurable **region**, RAM/storage tiers, tick rate, and backup cadence at checkout  \n- Display name field for how you appear in server browsers  \n\n**From \${$p}/mo** monthly.\n\n> Upgrade to **Silver** when modpacks start chewing disk and you need hourly safety nets.",
            ],
            'game|Silver' => [
                'short_description' => 'Twenty-four slots, daily backups, and mod-installer conveniences.',
                'description' => "## Silver game server\n\n**24 slots**, **12 GB RAM**, **60 GB NVMe**, **daily backups**, and a **mod installer** so your community spends time playing — not fiddling.\n\n### Highlights\n\n- Advanced **DDoS mitigation** for public listings  \n- Priority ticket queue with game-aware technicians  \n- Tick-rate upgrades priced transparently in the summary  \n\n**From \${$p}/mo** on monthly billing.\n\n```\nPro tip: pair hourly backups with a sensible world size cap.\n```",
            ],
            'game|Gold' => [
                'short_description' => 'Forty-eight slots, hourly backups, and premium DDoS scrubbing.',
                'description' => "## Gold game server\n\n**48 slots**, **24 GB RAM**, **120 GB NVMe**, **hourly backups**, and **premium DDoS scrubbing** for competitive communities.\n\n### Why Gold\n\n- Custom launch flags for modded experiences  \n- **24/7 game-specific** support with runbooks for popular stacks  \n- Automatic world backup tiers selectable at checkout  \n\n**From \${$p}/mo** monthly.\n\n| Add-on | Notes |\n| --- | --- |\n| Tick rate | 20 / 30 / 60 TPS options |\n| Regions | Mix of free and paid POPs |\n\n> **Bold promise:** if you outgrow Gold, our team helps plan a dedicated fleet migration.",
            ],
            default => [
                'short_description' => 'Universal service with transparent add-ons at checkout.',
                'description' => "## Plan overview\n\nThis package is delivered through our **Universal** connector so you can pick **regions**, **capacity**, and **security** add-ons with live pricing in the summary panel.\n\n**From \${$p}/mo** when you select the monthly billing cycle — quarterly and yearly cycles apply bundled discounts automatically.\n\n### Next steps\n\n1. Review configurable options on the left  \n2. Watch the summary update in real time  \n3. Add to cart when the totals match your budget  \n\n> Questions? Open a ticket from the client area and reference this package name.",
            ],
        };
    }

    /**
     * Create fake users, each with a primary address in a randomly chosen country.
     */
    private function seedFakeUsers(int $count): Collection
    {
        $users = collect();

        if ($count === 0) {
            return $users;
        }

        /** @var list<string> $countryCodes */
        $countryCodes = array_keys(World::countries());

        $this->newLine();
        $this->info("Creating {$count} fake users with addresses (random countries)...");

        $this->withProgressBar(range(1, $count), function () use ($countryCodes, $users): void {
            $countryCode = fake()->randomElement($countryCodes);

            $states = World::states($countryCode);
            $region = $states !== []
                ? (string) fake()->randomElement(array_keys($states))
                : fake()->words(2, true);

            $user = User::factory()->create([
                'country' => $countryCode,
            ]);

            $user->update([
                'avatar' => $this->initialsAvatarUrlForUser($user),
            ]);

            $user->refresh();

            $addressAttributes = [
                'address' => fake()->streetAddress(),
                'address2' => fake()->optional(0.25)->secondaryAddress(),
                'country' => $countryCode,
                'region' => $region,
                'city' => fake()->city(),
                'zip_code' => fake()->postcode(),
            ];

            $updated = $user->address()->update($addressAttributes);

            if ($updated === 0) {
                $user->address()->create($addressAttributes);
            }

            $registeredAt = $this->randomMomentWithinWindow();

            $this->touchModelAt($user, $registeredAt, $registeredAt, [
                'email_verified_at' => fake()->boolean(92) ? $registeredAt->copy()->addMinutes(random_int(1, 120)) : null,
                'last_login_at' => fake()->boolean(85) ? $this->randomMomentWithinWindow($registeredAt) : null,
                'last_seen_at' => fake()->boolean(80) ? $this->randomMomentWithinWindow($registeredAt) : null,
            ]);

            $users->push($user->fresh());
        });

        $this->newLine(2);

        return $users;
    }

    private function seedCommerceActivity(Collection $users, int $targetOrders, int $targetPayments, int $targetSubscriptions): void
    {
        if ($users->isEmpty() || ($targetOrders === 0 && $targetPayments === 0)) {
            return;
        }

        $baseGateway = GatewayConfig::query()
            ->where('is_active', true)
            ->where('is_staff_only', false)
            ->where('extension_identifier', '!=', 'gateway-balance')
            ->first();
        $fallbackRevenueGateway = GatewayConfig::query()
            ->where('extension_identifier', '!=', 'gateway-balance')
            ->first();

        $balanceGateway = GatewayConfig::balanceGateway();

        if (! $baseGateway && ! $fallbackRevenueGateway && ! $balanceGateway) {
            $this->warn('No gateway found. Skipping orders/payments simulation.');

            return;
        }

        $checkoutGateway = $baseGateway ?? $fallbackRevenueGateway ?? $balanceGateway;
        $currencyPool = Currency::query()
            ->where('is_active', true)
            ->where('currency', '!=', baseCurrency())
            ->pluck('currency')
            ->values();

        $pricePool = $this->buildWeightedPackagePricePool();

        if ($pricePool === []) {
            $this->warn('No active package prices found. Skipping orders/payments simulation.');

            return;
        }

        $orders = collect();
        $paymentsCreated = 0;

        if ($targetOrders > 0) {
            $this->newLine();
            $this->info("Simulating {$targetOrders} natural checkouts...");
        }

        $this->withProgressBar(range(1, $targetOrders), function () use ($users, $pricePool, $checkoutGateway, $currencyPool, &$orders, &$paymentsCreated): void {
            /** @var User $user */
            $user = $users->random();
            /** @var PackagePrice $packagePrice */
            $packagePrice = $this->weightedRandom($pricePool);
            $eventAt = $this->randomCommerceMoment($user->created_at ?? null);

            $maxCheckoutAttempts = 4;
            $checkoutAttempts = 0;
            $cart = null;
            $cartTotal = 0.0;

            while ($checkoutAttempts < $maxCheckoutAttempts) {
                $checkoutAttempts++;

                $cart = Cart::actions()->createCartForClient([
                    'session_id' => (string) Str::uuid(),
                    'user_id' => $user->id,
                ]);

                $useConfigOptions = $checkoutAttempts === 1 ? fake()->boolean(85) : false;

                Cart::actions()->addPackageToCart([
                    'cart_id' => $cart->id,
                    'package_price_id' => $packagePrice->id,
                    'config_options' => $useConfigOptions
                        ? $this->fakeConfigOptionsForPackage($packagePrice->package)
                        : [],
                ]);

                $cart->refresh()->load('items.options');
                $cartTotal = (float) $cart->total();

                if ($cartTotal <= self::MAX_SIMULATED_PAYMENT_AMOUNT) {
                    break;
                }

                $cart->clear();
                $packagePrice = $this->weightedRandom($pricePool);
            }

            if (! $cart || $cartTotal > self::MAX_SIMULATED_PAYMENT_AMOUNT) {
                return;
            }

            $basketIdentifier = Str::random(32);
            $orderItems = [];

            foreach ($cart->items as $item) {
                $orderItems[] = $item->createOrderItem($basketIdentifier);
            }

            $paymentCurrency = fake()->boolean(8) && $currencyPool->isNotEmpty()
                ? (string) $currencyPool->random()
                : baseCurrency();
            $payment = Payment::create([
                'user_id' => $user->id,
                'gateway_config_id' => $checkoutGateway->id,
                'description' => 'Payment for cart #'.$cart->id,
                'data' => [
                    'basket_identifier' => $basketIdentifier,
                    'cart_id' => $cart->id,
                    'order_items' => $orderItems,
                    'tax_details' => [
                        'country' => $user->country ?: 'US',
                        'region' => $user->address?->region,
                        'zip_code' => $user->address?->zip_code,
                    ],
                ],
                'currency' => $paymentCurrency,
                'handler' => CartCompletedHandler::class,
                'subtotal' => $cartTotal,
                'total' => $cartTotal,
                'earnings' => $cartTotal,
                'status' => 'unpaid',
            ]);

            $paymentStatus = $this->weightedRandom([
                ['value' => 'paid', 'weight' => 92],
                ['value' => 'unpaid', 'weight' => 5],
                ['value' => 'refunded', 'weight' => 3],
            ]);

            if ($paymentStatus === 'paid') {
                $payment->completed('SIM-CHECKOUT-'.Str::upper(Str::random(10)));
                $paymentsCreated++;
                $this->touchModelAt($payment, $eventAt, $eventAt, ['paid_at' => $eventAt]);
            } elseif ($paymentStatus === 'refunded') {
                $payment->completed('SIM-CHECKOUT-'.Str::upper(Str::random(10)));
                $payment->update([
                    'status' => 'refunded',
                    'earnings' => 0,
                ]);
                $paymentsCreated++;
                $this->touchModelAt($payment, $eventAt, $eventAt, ['paid_at' => $eventAt]);
            } else {
                $this->touchModelAt($payment, $eventAt, $eventAt, ['paid_at' => null]);
            }

            $createdOrder = null;
            if ($paymentStatus !== 'unpaid') {
                $createdOrder = Order::query()->latest('id')->where('user_id', $user->id)->first();
            }

            if ($createdOrder) {
                $status = fake()->randomElement(['active', 'active', 'active', 'active', 'pending', 'suspended', 'terminated']);
                $dueDate = $createdOrder->isRecurring() ? $eventAt->copy()->addDays((int) $createdOrder->period_in_days) : null;

                $this->touchModelAt($createdOrder, $eventAt, $eventAt, [
                    'status' => $status,
                    'last_renewed_at' => $eventAt,
                    'due_date' => $dueDate,
                ]);

                $orders->push($createdOrder->fresh());
            }

            $this->touchModelAt($cart, $eventAt, $eventAt);
        });

        if ($targetOrders > 0) {
            $this->newLine(2);
        }

        if ($targetPayments > $paymentsCreated) {
            $remaining = $targetPayments - $paymentsCreated;
            $this->info("Creating {$remaining} additional realistic payments (renewals/top-ups)...");

            $this->withProgressBar(range(1, $remaining), function () use ($users, $orders, $checkoutGateway, $balanceGateway, $currencyPool): void {
                $renewableOrders = $orders
                    ->filter(fn (Order $order) => (int) $order->period_in_days > 0 && $order->status !== 'terminated')
                    ->values();
                $runRenewal = $renewableOrders->isNotEmpty() && fake()->boolean(62);

                if ($runRenewal) {
                    /** @var Order $order */
                    $order = $renewableOrders->random();
                    $maxRenewalDays = (int) floor(self::MAX_SIMULATED_PAYMENT_AMOUNT / max(0.000001, (float) $order->daily_price));
                    if ($maxRenewalDays < 1) {
                        return;
                    }
                    $renewalDays = max(1, min((int) $order->period_in_days, $maxRenewalDays));
                    $renewedAt = $this->randomCommerceMoment($order->created_at ?? null);

                    $renewalPayment = Payment::actions()->renewalPaymentForClient([
                        'order_id' => $order->id,
                        'renewal_days' => $renewalDays,
                    ]);

                    $renewalCurrency = fake()->boolean(8) && $currencyPool->isNotEmpty()
                        ? (string) $currencyPool->random()
                        : baseCurrency();
                    $renewalPayment->update([
                        'gateway_config_id' => $checkoutGateway?->id ?? $balanceGateway?->id,
                        'currency' => $renewalCurrency,
                    ]);
                    $renewalStatus = $this->weightedRandom([
                        ['value' => 'paid', 'weight' => 90],
                        ['value' => 'unpaid', 'weight' => 6],
                        ['value' => 'refunded', 'weight' => 4],
                    ]);

                    if ($renewalStatus === 'paid') {
                        $renewalPayment->completed('SIM-RENEW-'.Str::upper(Str::random(10)));
                        $this->touchModelAt($renewalPayment, $renewedAt, $renewedAt, ['paid_at' => $renewedAt]);

                        Order::actions()->renewOrderAsClient([
                            'order_id' => $order->id,
                            'renewal_days' => $renewalDays,
                        ]);
                    } elseif ($renewalStatus === 'refunded') {
                        $renewalPayment->completed('SIM-RENEW-'.Str::upper(Str::random(10)));
                        $renewalPayment->update([
                            'status' => 'refunded',
                            'earnings' => 0,
                        ]);
                        $this->touchModelAt($renewalPayment, $renewedAt, $renewedAt, ['paid_at' => $renewedAt]);
                    } else {
                        $this->touchModelAt($renewalPayment, $renewedAt, $renewedAt, ['paid_at' => null]);
                    }

                    $order->refresh();
                    $this->touchModelAt($order, $order->created_at ?? $renewedAt, $renewedAt, [
                        'last_renewed_at' => $renewedAt,
                    ]);

                    return;
                }

                /** @var User $user */
                $user = $users->random();
                $topupAt = $this->randomCommerceMoment($user->created_at ?? null);
                $amount = fake()->randomFloat(2, 5, self::MAX_SIMULATED_PAYMENT_AMOUNT);

                $topup = PaymentActions::createBalancePaymentForClient([
                    'user_id' => $user->id,
                    'amount' => $amount,
                ]);

                $topupCurrency = fake()->boolean(6) && $currencyPool->isNotEmpty()
                    ? (string) $currencyPool->random()
                    : baseCurrency();
                if ($balanceGateway) {
                    $topup->update([
                        'gateway_config_id' => $balanceGateway->id,
                        'currency' => $topupCurrency,
                    ]);
                }
                $topupStatus = $this->weightedRandom([
                    ['value' => 'paid', 'weight' => 91],
                    ['value' => 'unpaid', 'weight' => 6],
                    ['value' => 'refunded', 'weight' => 3],
                ]);

                if ($topupStatus === 'paid') {
                    $topup->completed('SIM-TOPUP-'.Str::upper(Str::random(10)));
                    $this->touchModelAt($topup, $topupAt, $topupAt, ['paid_at' => $topupAt]);
                } elseif ($topupStatus === 'refunded') {
                    $topup->completed('SIM-TOPUP-'.Str::upper(Str::random(10)));
                    $topup->update([
                        'status' => 'refunded',
                        'earnings' => 0,
                    ]);
                    $this->touchModelAt($topup, $topupAt, $topupAt, ['paid_at' => $topupAt]);
                } else {
                    $this->touchModelAt($topup, $topupAt, $topupAt, ['paid_at' => null]);
                }
            });

            $this->newLine(2);
        }

        $this->seedOrderSubscriptions($orders, $checkoutGateway, $currencyPool, $targetSubscriptions);
    }

    private function seedOrderSubscriptions(
        Collection $orders,
        GatewayConfig $gatewayConfig,
        Collection $currencyPool,
        int $targetSubscriptions
    ): void {
        if ($targetSubscriptions <= 0 || $orders->isEmpty()) {
            return;
        }

        $eligibleOrders = $orders
            ->filter(function (Order $order): bool {
                return (int) $order->period_in_days > 0
                    && $order->status !== 'terminated'
                    && (float) $order->price <= self::MAX_SIMULATED_PAYMENT_AMOUNT;
            })
            ->values();

        if ($eligibleOrders->isEmpty()) {
            $this->warn('No eligible recurring orders for subscription seeding.');

            return;
        }

        $count = min($targetSubscriptions, $eligibleOrders->count());

        $this->info("Creating {$count} order-linked subscriptions...");

        $selectedOrders = $eligibleOrders->shuffle()->take($count)->values();

        $this->withProgressBar(range(1, $selectedOrders->count()), function ($index) use ($selectedOrders, $gatewayConfig, $currencyPool): void {
            /** @var Order $order */
            $order = $selectedOrders[(int) $index - 1];
            $subscriptionCreatedAt = $this->randomCommerceMoment($order->created_at ?? null);

            $gatewayConfig = GatewayConfig::query()
                ->where('is_active', true)
                ->where('extension_identifier', '!=', 'gateway-balance')
                ->where('type', 'subscription')
                ->first();

            $subscription = Order::actions()->createSubscriptionAsClient([
                'order_id' => $order->id,
                'gateway_config_id' => $gatewayConfig->id,
                'user_id' => $order->user_id,
            ]);

            /** @var Subscription $subscription */
            $subscription = Subscription::query()->findOrFail($subscription->id);
            $subscriptionCurrency = fake()->boolean(8) && $currencyPool->isNotEmpty()
                ? (string) $currencyPool->random()
                : baseCurrency();

            $subscription->update([
                'currency' => $subscriptionCurrency,
                'amount' => min((float) $subscription->amount, self::MAX_SIMULATED_PAYMENT_AMOUNT),
            ]);

            $status = $this->weightedRandom([
                ['value' => 'active', 'weight' => 78],
                ['value' => 'cancelled', 'weight' => 12],
                ['value' => 'inactive', 'weight' => 5],
                ['value' => 'pending', 'weight' => 5],
            ]);

            if ($status !== 'pending') {
                $nextBillingAt = $subscriptionCreatedAt->copy()->addDays(max(1, (int) $subscription->frequency));
                $subscription->activated('SIM-SUB-'.Str::upper(Str::random(10)), $nextBillingAt);

                $this->touchModelAt($subscription, $subscriptionCreatedAt, $subscriptionCreatedAt, [
                    'activated_at' => $subscriptionCreatedAt,
                    'next_billing_at' => $nextBillingAt,
                ]);

                $orderSubscription = OrderSubscription::query()
                    ->where('subscription_id', $subscription->id)
                    ->first();

                if ($orderSubscription) {
                    $this->touchModelAt($orderSubscription, $subscriptionCreatedAt, $subscriptionCreatedAt);
                }
            } else {
                $this->touchModelAt($subscription, $subscriptionCreatedAt, $subscriptionCreatedAt, [
                    'activated_at' => null,
                    'next_billing_at' => null,
                ]);
            }

            if ($status === 'cancelled') {
                $cancelledAt = $this->randomMomentWithinWindow($subscriptionCreatedAt, now());
                $subscription->cancelled('Cancelled by data simulation');
                $this->touchModelAt($subscription, $subscriptionCreatedAt, $cancelledAt, [
                    'cancelled_at' => $cancelledAt,
                    'next_billing_at' => $subscription->next_billing_at ?: $cancelledAt->copy()->addDays(max(1, (int) $subscription->frequency)),
                ]);
            }

            if ($status === 'inactive') {
                $inactiveAt = $this->randomMomentWithinWindow($subscriptionCreatedAt, now());
                $subscription->inactive();
                $this->touchModelAt($subscription, $subscriptionCreatedAt, $inactiveAt, [
                    'last_checked_at' => $inactiveAt,
                ]);
            }
        });

        $this->newLine(2);
    }

    /**
     * @return array<int, array{value: PackagePrice, weight: int}>
     */
    private function buildWeightedPackagePricePool(): array
    {
        return PackagePrice::query()
            ->with('package.configOptions')
            ->whereHas('package', fn ($query) => $query->where('status', 'active'))
            ->get()
            ->map(function (PackagePrice $price): array {
                $monthlyPrice = (float) $price->price;
                $weight = max(3, 120 - (int) min(80, $monthlyPrice));

                if ($price->period_in_days === 30) {
                    $weight += 24;
                } elseif ($price->period_in_days >= 365) {
                    $weight -= 8;
                }

                $name = Str::lower($price->package->name);
                if (str_contains($name, 'starter') || str_contains($name, 'personal') || str_contains($name, 'small') || str_contains($name, 'essentials')) {
                    $weight += 18;
                }
                if (str_contains($name, 'enterprise') || str_contains($name, 'elite') || str_contains($name, 'xl')) {
                    $weight = max(1, $weight - 20);
                }

                return [
                    'value' => $price,
                    'weight' => max(1, $weight),
                ];
            })
            ->all();
    }

    private function fakeConfigOptionsForPackage(Package $package): array
    {
        $package->loadMissing('configOptions');
        $options = [];

        foreach ($package->configOptions as $configOption) {
            $data = $configOption->data ?? [];

            if (in_array($configOption->type, ['select', 'radio'], true)) {
                $available = collect($data['options'] ?? [])->pluck('value')->filter()->values();
                if ($available->isNotEmpty()) {
                    $options[$configOption->key] = fake()->boolean(65)
                        ? ($configOption->default_value ?: $available->first())
                        : $available->random();
                }

                continue;
            }

            if (in_array($configOption->type, ['range', 'number'], true)) {
                $min = (int) ($data['min'] ?? $data['min_value'] ?? 0);
                $max = (int) ($data['max'] ?? $data['max_value'] ?? $min);
                $step = max(1, (int) ($data['step'] ?? $data['step_value'] ?? 1));
                $free = (int) ($data['free_value'] ?? $min);

                if (fake()->boolean(60)) {
                    $options[$configOption->key] = (string) $free;
                } else {
                    $raw = random_int($min, $max);
                    $adjusted = $min + (int) (floor(($raw - $min) / $step) * $step);
                    $options[$configOption->key] = (string) min($max, max($min, $adjusted));
                }

                continue;
            }

            if (in_array($configOption->type, ['text', 'password'], true)) {
                $options[$configOption->key] = $configOption->type === 'password'
                    ? Str::password(14)
                    : (string) ($configOption->default_value ?: fake()->domainName());
            }
        }

        return $options;
    }

    private function randomMomentWithinWindow(?Carbon $from = null, ?Carbon $to = null): Carbon
    {
        $start = $from?->copy() ?? now()->subYears(2);
        $end = $to?->copy() ?? now();

        if ($start->greaterThan($end)) {
            $start = $end->copy()->subMinute();
        }

        return Carbon::instance(fake()->dateTimeBetween($start, $end));
    }

    private function randomCommerceMoment(?Carbon $from = null): Carbon
    {
        if (fake()->boolean(35)) {
            return $this->randomMomentWithinWindow(now()->subDays(120), now());
        }

        return $this->randomMomentWithinWindow($from, now());
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function touchModelAt(Model $model, Carbon $createdAt, Carbon $updatedAt, array $extra = []): void
    {
        $model->timestamps = false;

        $model->forceFill(array_merge($extra, [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]))->saveQuietly();

        $model->timestamps = true;
    }

    /**
     * @param  array<int, array{value: mixed, weight: int}>  $weightedItems
     */
    private function weightedRandom(array $weightedItems): mixed
    {
        $totalWeight = array_sum(array_column($weightedItems, 'weight'));
        $pick = random_int(1, max(1, $totalWeight));
        $running = 0;

        foreach ($weightedItems as $item) {
            $running += $item['weight'];
            if ($pick <= $running) {
                return $item['value'];
            }
        }

        return $weightedItems[array_key_last($weightedItems)]['value'];
    }

    /**
     * DiceBear initials avatar (SVG) from the user's name.
     */
    private function initialsAvatarUrlForUser(User $user): string
    {
        $seed = trim(implode(' ', array_filter([$user->first_name, $user->last_name])));

        if ($seed === '') {
            $seed = $user->username ?: 'User';
        }

        return sprintf(
            'https://api.dicebear.com/9.x/initials/svg?seed=%s',
            rawurlencode($seed)
        );
    }
}
