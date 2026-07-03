<?php

namespace App\Repositories;

use App\Exceptions\Catalog\SubscriptionOrderItemNotFoundException;
use App\Models\Customer;
use App\Models\SubscriptionOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionOrderItemRepository
{
    /** @return LengthAwarePaginator<int, SubscriptionOrderItem> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return SubscriptionOrderItem::query()
            ->with('subscriptionOrder')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('product_title', 'like', "%{$search}%")
                        ->orWhere('variant_title', 'like', "%{$search}%")
                        ->orWhere('time_slot', 'like', "%{$search}%")
                        ->orWhereHas('subscriptionOrder', function ($query) use ($search): void {
                            $query->where('transaction_id', 'like', "%{$search}%")
                                ->orWhere('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): SubscriptionOrderItem
    {
        return SubscriptionOrderItem::query()->create($attributes)->load('subscriptionOrder');
    }

    public function find(int $id): SubscriptionOrderItem
    {
        $item = SubscriptionOrderItem::query()->with('subscriptionOrder')->find($id);

        if (! $item) {
            throw new SubscriptionOrderItemNotFoundException;
        }

        return $item;
    }

    public function findForCustomerSubscriptionOrder(Customer $customer, int $orderId, int $itemId): SubscriptionOrderItem
    {
        $item = SubscriptionOrderItem::query()
            ->with('subscriptionOrder')
            ->whereKey($itemId)
            ->where('subscription_order_id', $orderId)
            ->whereHas('subscriptionOrder', function ($query) use ($customer): void {
                $query->whereBelongsTo($customer);
            })
            ->first();

        if (! $item) {
            throw new SubscriptionOrderItemNotFoundException;
        }

        return $item;
    }

    /** @param array<string, mixed> $attributes */
    public function update(SubscriptionOrderItem $item, array $attributes): SubscriptionOrderItem
    {
        $item->update($attributes);

        return $item->refresh()->load('subscriptionOrder');
    }

    public function delete(SubscriptionOrderItem $item): void
    {
        $item->delete();
    }
}
