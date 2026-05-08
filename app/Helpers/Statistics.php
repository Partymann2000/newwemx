<?php

namespace App\Helpers;

use App\Models\Address;
use App\Models\Currency;
use App\Models\GatewayConfig;
use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Statistics
{
    public static function revenueFromPaymentsLastDays(int $days = 30, string $toCurrency = 'USD')
    {
        return Cache::remember("revenue_from_payments_last_{$days}_days_to_{$toCurrency}", now()->addMinutes(5), function () use ($days, $toCurrency) {
            // Gateways to exclude
            $balanceGatewayIds = GatewayConfig::where('extension_identifier', 'gateway-balance')
                ->pluck('id');

            $now = now();
            $currentStart  = $now->clone()->subDays($days);          // [currentStart, now]
            $previousStart = $now->clone()->subDays($days * 2);      // [previousStart, currentStart)

            // Helper to compute SUM(earnings) per currency + total count for a given window
            $computeWindow = function (Carbon $from, Carbon $to) use ($balanceGatewayIds) {
                // revenue grouped by currency
                $byCurrency = Payment::query()
                    ->where('status', 'paid')
                    ->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$from, $to])
                    ->whereNotIn('gateway_config_id', $balanceGatewayIds)
                    ->selectRaw('currency, SUM(earnings) as amount')
                    ->groupBy('currency')
                    ->pluck('amount', 'currency')
                    ->toArray();

                // payment count (no grouping)
                $count = Payment::query()
                    ->where('status', 'paid')
                    ->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$from, $to])
                    ->whereNotIn('gateway_config_id', $balanceGatewayIds)
                    ->count();

                return [$byCurrency, $count];
            };

            // Current window
            [$currentByCurrency, $currentCount] = $computeWindow($currentStart, $now);

            // Previous window (immediately before the current one)
            [$previousByCurrency, $previousCount] = $computeWindow($previousStart, $currentStart);

            // Convert revenue to the requested currency
            $convertTotals = function (array $byCurrency) use ($toCurrency) {
                $total = 0.0;
                foreach ($byCurrency as $currency => $amount) {
                    // If DB driver returns strings, (float) will normalize
                    $amount = (float) $amount;

                    if ($currency === $toCurrency) {
                        $total += $amount;
                        continue;
                    }

                    try {
                        $total += Currency::convert($amount, $currency, $toCurrency);
                    } catch (\Throwable $e) {
                        // Skip currencies we fail to convert
                        continue;
                    }
                }
                return $total;
            };

            $currentAmount  = $convertTotals($currentByCurrency);
            $previousAmount = $convertTotals($previousByCurrency);

            // Percentage change vs previous period (null if previous is 0 to avoid div-by-zero)
            $changePct = $previousAmount > 0
                ? (($currentAmount - $previousAmount) / $previousAmount) * 100
                : null;

            if ($changePct !== null) {
                // Round to 1 decimal place for display
                $changePct = round($changePct, 0, PHP_ROUND_HALF_UP);
            }

            return [
                'currency' => $toCurrency,
                'amount' => $currentAmount,
                'payment_count' => $currentCount,
                'currency_count' => count($currentByCurrency),
                'change_compared_to_previous_period' => $changePct,
                'previous_amount' => $previousAmount,
                'previous_payment_count' => $previousCount,
                'window_days' => $days,
            ];
        });
    }

    public static function newRegistrationsLastDays($days = 30): array
    {
        $now = now();
        $currentStart  = $now->clone()->subDays($days);          // [currentStart, now]
        $previousStart = $now->clone()->subDays($days * 2);      // [previousStart, currentStart)

        // Current window
        $currentCount = User::whereBetween('created_at', [$currentStart, $now])->count();

        // Previous window (immediately before the current one)
        $previousCount = User::whereBetween('created_at', [$previousStart, $currentStart])->count();

        // Percentage change vs previous period (null if previous is 0 to avoid div-by-zero)
        $changePct = $previousCount > 0
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : null;

        if ($changePct !== null) {
            // Round to 1 decimal place for display
            $changePct = round($changePct, 0, PHP_ROUND_HALF_UP);
        }

        return [
            'count' => $currentCount,
            'change_compared_to_previous_period' => $changePct,
            'previous_count' => $previousCount,
            'window_days' => $days,
        ];
    }

    public static function newOrdersLastDays($days = 30): array
    {
        $now = now();
        $currentStart  = $now->clone()->subDays($days);          // [currentStart, now]
        $previousStart = $now->clone()->subDays($days * 2);      // [previousStart, currentStart)

        // Current window
        $currentCount = Order::whereBetween('created_at', [$currentStart, $now])->count();

        // Previous window (immediately before the current one)
        $previousCount = Order::whereBetween('created_at', [$previousStart, $currentStart])->count();

        // Percentage change vs previous period (null if previous is 0 to avoid div-by-zero)
        $changePct = $previousCount > 0
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : null;

        if ($changePct !== null) {
            // Round to 1 decimal place for display
            $changePct = round($changePct, 0, PHP_ROUND_HALF_UP);
        }

        return [
            'count' => $currentCount,
            'change_compared_to_previous_period' => $changePct,
            'previous_count' => $previousCount,
            'window_days' => $days,
        ];
    }

    public static function paidPaymentCountAllTime()
    {
        return Payment::where('status', 'paid')->whereNotNull('paid_at')->count();
    }

    public static function refundedPaymentCountAllTime()
    {
        return Payment::where('status', 'refunded')->count();
    }

    public static function activeOrderCountAllTime()
    {
        return Order::where('status', 'active')->count();
    }

    public static function suspendedOrderCountAllTime()
    {
        return Order::where('status', 'suspended')->count();
    }

    public static function terminatedOrderCountAllTime()
    {
        return Order::where('status', 'terminated')->count();
    }

    public static function userCountAllTime()
    {
        return User::count();
    }

    public static function uniqueCountryCountAllTime()
    {
        return Address::distinct('country')->count('country');
    }
}
