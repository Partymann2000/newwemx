<?php

namespace App\Handlers;
use App\Models\Order;
use App\Models\Payment;

class OrderRenewalHandler
{
    public function onPaymentCompleted(Payment $payment)
    {
        $renewalDays = $payment->data('renewal_days', 0);
        $order = $payment->payable;

        if ($renewalDays > 0 && $order) {
            Order::actions()->renewOrderAsClient([
                'order_id' => $order->id,
                'renewal_days' => $renewalDays,
            ]);
        }
    }
}
