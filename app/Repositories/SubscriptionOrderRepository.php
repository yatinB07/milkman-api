<?php

namespace App\Repositories;

use App\Exceptions\Catalog\SubscriptionOrderNotFoundException;
use App\Models\SubscriptionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionOrderRepository
{
    /** @return LengthAwarePaginator<int, SubscriptionOrder> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return SubscriptionOrder::query()
            ->with(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items'])
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_mobile', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('order_type', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        })
                        ->orWhereHas('customer', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        })
                        ->orWhereHas('rider', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('ordered_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): SubscriptionOrder
    {
        return SubscriptionOrder::query()->create($attributes)->load(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items']);
    }

    /** @param array<int, array<string, mixed>> $items */
    public function createWithItems(array $attributes, array $items): SubscriptionOrder
    {
        $order = SubscriptionOrder::query()->create($attributes);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'items']);
    }

    public function find(int $id): SubscriptionOrder
    {
        $order = SubscriptionOrder::query()
            ->with(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items'])
            ->find($id);

        if (! $order) {
            throw new SubscriptionOrderNotFoundException;
        }

        return $order;
    }

    /** @param array<string, mixed> $attributes */
    public function update(SubscriptionOrder $order, array $attributes): SubscriptionOrder
    {
        $order->update($attributes);

        return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items']);
    }

    public function delete(SubscriptionOrder $order): void
    {
        $order->delete();
    }
}
