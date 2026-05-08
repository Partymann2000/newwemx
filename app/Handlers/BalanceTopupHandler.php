<?php

namespace App\Handlers;
use App\Models\Payment;

class BalanceTopupHandler
{
    public function onPaymentCompleted(Payment $payment)
    {
        $name = "Payment #{$payment->id}";

        if ($payment->gatewayConfig) {
            $name = $payment->gatewayConfig->display_name;
        }

        $payment->user->updateBalance('+', $payment->data('amount', 0), "Balance top-up via {$name}");
    }
}
