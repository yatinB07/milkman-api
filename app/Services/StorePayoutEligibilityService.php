<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use App\Models\SubscriptionOrder;

class StorePayoutEligibilityService
{
    public function availableEarnings(Store $store): float
    {
        return $this->completedNormalOrderNet($store)
            + $this->completedSubscriptionOrderNet($store)
            - (float) $store->payoutRequests()->sum('amount');
    }

    public function canWithdraw(Store $store, float $amount): bool
    {
        return $amount <= $this->availableEarnings($store);
    }

    private function completedNormalOrderNet(Store $store): float
    {
        return (float) Order::query()
            ->whereBelongsTo($store)
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (Order $order): float => $this->storeNet($order));
    }

    private function completedSubscriptionOrderNet(Store $store): float
    {
        return (float) SubscriptionOrder::query()
            ->whereBelongsTo($store)
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (SubscriptionOrder $order): float => $this->storeNet($order));
    }

    private function storeNet(Order|SubscriptionOrder $order): float
    {
        $gross = ((float) $order->getAttribute('subtotal'))
            - ((float) $order->getAttribute('coupon_amount'))
            + ((float) $order->getAttribute('delivery_charge'));

        return $gross - ($gross * ((float) $order->getAttribute('commission_percent') / 100));
    }
}
