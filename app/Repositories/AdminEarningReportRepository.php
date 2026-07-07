<?php

namespace App\Repositories;

use App\Data\Admin\ListQueryData;
use App\Models\Order;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminEarningReportRepository
{
    public function paginate(ListQueryData $data): LengthAwarePaginator
    {
        $query = Store::query()
            ->select(['id', 'title', 'email', 'mobile', 'rating'])
            ->orderBy('title');

        if ($data->search !== null) {
            $query->where(function ($query) use ($data): void {
                $query->where('title', 'like', "%{$data->search}%")
                    ->orWhere('email', 'like', "%{$data->search}%")
                    ->orWhere('mobile', 'like', "%{$data->search}%");
            });
        }

        return $query
            ->paginate($data->perPage)
            ->through(fn (Store $store): array => $this->reportForStore($store));
    }

    /** @return array<string, mixed> */
    private function reportForStore(Store $store): array
    {
        $normalOrders = Order::query()
            ->where('store_id', $store->getKey())
            ->where('status', 'Completed')
            ->get();
        $subscriptionOrders = SubscriptionOrder::query()
            ->where('store_id', $store->getKey())
            ->where('status', 'Completed')
            ->get();

        $normalGross = $normalOrders->sum(fn (Order $order): float => $this->gross($order));
        $subscriptionGross = $subscriptionOrders->sum(fn (SubscriptionOrder $order): float => $this->gross($order));
        $normalCommission = $normalOrders->sum(fn (Order $order): float => $this->commission($order));
        $subscriptionCommission = $subscriptionOrders->sum(fn (SubscriptionOrder $order): float => $this->commission($order));
        $storePayout = (float) $store->payoutRequests()->sum('amount');
        $storeEarning = ($normalGross - $normalCommission) + ($subscriptionGross - $subscriptionCommission);

        return [
            'store' => [
                'id' => $store->getKey(),
                'title' => $store->getAttribute('title'),
                'email' => $store->getAttribute('email'),
                'mobile' => $store->getAttribute('mobile'),
            ],
            'sale_count' => $normalOrders->count() + $subscriptionOrders->count(),
            'total_amount' => $this->money($normalGross + $subscriptionGross),
            'cash_on_hand_amount' => $this->money(max(0, $normalGross - (float) $store->cashCollections()->sum('amount'))),
            'delivery_charge' => $this->money(
                (float) $normalOrders->sum('delivery_charge') + (float) $subscriptionOrders->sum('delivery_charge')
            ),
            'platform_earning' => $this->money($normalCommission + $subscriptionCommission),
            'store_payout' => $this->money($storePayout),
            'store_remaining_amount' => $this->money($storeEarning - $storePayout),
            'rating' => $this->rating($store, $normalOrders, $subscriptionOrders),
        ];
    }

    private function gross(Order|SubscriptionOrder $order): float
    {
        return ((float) $order->getAttribute('subtotal'))
            - ((float) $order->getAttribute('coupon_amount'))
            + ((float) $order->getAttribute('delivery_charge'));
    }

    private function commission(Order|SubscriptionOrder $order): float
    {
        return $this->gross($order) * ((float) $order->getAttribute('commission_percent') / 100);
    }

    /**
     * @param  Collection<int, Order>  $normalOrders
     * @param  Collection<int, SubscriptionOrder>  $subscriptionOrders
     * @return array{average: string, count: int}
     */
    private function rating(Store $store, $normalOrders, $subscriptionOrders): array
    {
        $normalRated = $normalOrders->where('total_rating', '>', 0);
        $subscriptionRated = $subscriptionOrders->where('total_rating', '>', 0);

        $normalAverage = $normalRated->isNotEmpty() ? (float) $normalRated->avg('total_rating') : null;
        $subscriptionAverage = $subscriptionRated->isNotEmpty() ? (float) $subscriptionRated->avg('total_rating') : null;

        $average = match (true) {
            $normalAverage !== null && $subscriptionAverage !== null => ($normalAverage + $subscriptionAverage) / 2,
            $normalAverage !== null => $normalAverage,
            $subscriptionAverage !== null => $subscriptionAverage,
            default => (float) $store->getAttribute('rating'),
        };

        return [
            'average' => $this->money($average),
            'count' => $normalRated->count() + $subscriptionRated->count(),
        ];
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
