<?php

namespace App\Repositories;

use App\Exceptions\Catalog\OrderItemNotFoundException;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderItemRepository
{
    /** @return LengthAwarePaginator<int, OrderItem> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return OrderItem::query()
            ->with('order')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('product_title', 'like', "%{$search}%")
                        ->orWhere('variant_title', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($query) use ($search): void {
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
    public function create(array $attributes): OrderItem
    {
        return OrderItem::query()->create($attributes)->load('order');
    }

    public function find(int $id): OrderItem
    {
        $item = OrderItem::query()->with('order')->find($id);

        if (! $item) {
            throw new OrderItemNotFoundException;
        }

        return $item;
    }

    /** @param array<string, mixed> $attributes */
    public function update(OrderItem $item, array $attributes): OrderItem
    {
        $item->update($attributes);

        return $item->refresh()->load('order');
    }

    public function delete(OrderItem $item): void
    {
        $item->delete();
    }
}
