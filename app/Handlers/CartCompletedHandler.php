<?php

namespace App\Handlers;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\CartOrderItem;

class CartCompletedHandler
{
    public function onPaymentCompleted(Payment $payment)
    {
        $cardId = $payment->data('cart_id', null);
        $orderItems = $payment->data('order_items', []);
        $items = CartOrderItem::whereIn('id', $orderItems)->get();

        foreach($items as $item) {
            // set the item as paid
            $item->is_paid = true;
            $item->save();

            // for each item in the cart, call the handler
            foreach(range(1, $item->quantity) as $i) {
                $item->completed();
            }
        }

        // if the cart id is provided, check if it exists, then clear it
        $cart = Cart::find($cardId);

        if ($cart) {
            $cart->clear();
        }
    }
}
