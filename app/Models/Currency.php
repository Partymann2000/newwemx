<?php

namespace App\Models;

use App\Actions\CurrencyActions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Currency extends Model
{
    protected $table = 'currencies';

    protected $primaryKey = 'currency';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'currency',
        'display_name',
        'market_rate',
        'manual_rate',
        'previous_rate',
        'is_active',
        'sort_order',
        'rate_updated_at',
        'use_manual_rate',
    ];

    protected $casts = [
        'market_rate' => 'decimal:8',
        'manual_rate' => 'decimal:8',
        'previous_rate' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'rate_updated_at' => 'datetime',
        'use_manual_rate' => 'boolean',
    ];

    public function getRate(): float
    {
        return $this->use_manual_rate && $this->manual_rate ? $this->manual_rate : $this->market_rate;
    }

    public function rateLastUpdatedAt(): string
    {
        return $this->rate_updated_at ? $this->rate_updated_at->diffForHumans() : 'Never';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /** @var array<string,self|null> */
    protected static array $modelMemo = [];

    /** @var array<string,int> */
    protected static array $scaleMemo = [];

    protected static function getCurrencyModel(string $code): ?self
    {
        $code = strtoupper($code);

        // Hit the in-request cache first
        if (array_key_exists($code, self::$modelMemo)) {
            return self::$modelMemo[$code];
        }

        // Single query; subsequent calls reuse it
        return self::$modelMemo[$code] = self::query()->find($code);
    }

    protected static function getScale(string $code): int
    {
        $code = strtoupper($code);

        return self::$scaleMemo[$code] ??= self::scaleFor($code);
    }

    public static function convert(float|int $amount, string $fromCurrencyCode, string $toCurrencyCode)
    {
        $fromCurrency = self::getCurrencyModel($fromCurrencyCode);
        $toCurrency = self::getCurrencyModel($toCurrencyCode);

        if (! $fromCurrency) {
            throw new \Exception("Currency with code {$fromCurrencyCode} not found");
        }
        if (! $toCurrency) {
            throw new \Exception("Currency with code {$toCurrencyCode} not found");
        }

        $fromRate = (float) $fromCurrency->getRate();
        $toRate = (float) $toCurrency->getRate();

        if ($fromRate <= 0.0) {
            throw new \Exception("Invalid or zero rate for {$fromCurrencyCode}");
        }
        if ($toRate <= 0.0) {
            throw new \Exception("Invalid or zero rate for {$toCurrencyCode}");
        }

        $raw = ($amount / $fromRate) * $toRate;

        $scale = self::getScale($toCurrency->currency);
        $rounded = round($raw, $scale, PHP_ROUND_HALF_UP);

        return $scale === 0 ? (int) $rounded : $rounded;
    }

    /**
     * Determine decimal scale for a currency code.
     * If you later add a `decimals` column, read it here instead.
     */
    protected static function scaleFor(string $code): int
    {
        $code = strtoupper($code);

        // Zero-decimal currencies (no active minor unit)
        // Extend this list to your needs.
        static $zeroDecimal = [
            'JPY', 'HUF', 'TWD', 'KRW', 'VND', 'IDR', 'ISK', 'CLP', 'RWF', 'UGX', 'XAF', 'XOF', 'XPF', 'MGA',
        ];

        // 3-decimal currencies
        static $threeDecimal = ['KWD', 'BHD', 'JOD', 'OMR', 'TND', 'LYD', 'IQD'];

        if (in_array($code, $zeroDecimal, true)) {
            return 0;
        }
        if (in_array($code, $threeDecimal, true)) {
            return 3;
        }

        // Default
        return 2;
    }

    public function scopeSearch($query, string $search): void
    {
        if ($search) {
            $query->where('currency', 'like', '%'.$search.'%')
                ->orWhere('display_name', 'like', '%'.$search.'%');
        }
    }

    public static function updateCurrencyRates()
    {
        $currencies = Currency::all();
        $rates = self::getRatesFromApi();

        foreach ($currencies as $currency) {
            if (isset($rates['rates'][$currency->currency])) {
                $newRate = round($rates['rates'][$currency->currency], 2);

                $currency->previous_rate = $currency->getRate();
                $currency->market_rate = $newRate;
                $currency->rate_updated_at = now();
                $currency->save();
            }
        }
    }

    protected static function getRatesFromApi()
    {
        return Cache::remember('currency_rates', 3600, function () {
            $response = Http::get('https://open.er-api.com/v6/latest/USD');

            if (! $response->successful()) {
                throw new \Exception('Failed to fetch currency rates from the API');
            }

            return $response->json();
        });
    }

    public static function actions(): CurrencyActions
    {
        return new CurrencyActions;
    }
}
