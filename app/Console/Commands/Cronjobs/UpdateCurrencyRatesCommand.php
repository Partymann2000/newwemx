<?php

namespace App\Console\Commands\Cronjobs;

use Illuminate\Console\Command;
use App\Models\Currency;

class UpdateCurrencyRatesCommand extends Command
{
    protected $signature = 'cronjobs:update-currency-rates';

    protected $description = 'Command description';

    public function handle(): void
    {
        try {
            Currency::updateCurrencyRates();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
