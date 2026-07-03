<?php

namespace App\Repositories;

use App\Exceptions\Catalog\OrderNotFoundException;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /** @return LengthAwarePaginator<int, Order> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
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
    public function create(array $attributes): Order
    {
        return Order::query()->create($attributes)->load(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items']);
    }

    /** @param array<int, array<string, mixed>> $items */
    public function createWithItems(array $attributes, array $items): Order
    {
        $order = Order::query()->create($attributes);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'items']);
    }

    public function find(int $id): Order
    {
        $order = Order::query()
            ->with(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items'])
            ->find($id);

        if (! $order) {
            throw new OrderNotFoundException;
        }

        return $order;
    }

    /** @return LengthAwarePaginator<int, Order> */
    public function paginateForCustomer(Customer $customer, string $status, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['store', 'paymentMethod', 'rider', 'items'])
            ->whereBelongsTo($customer)
            ->when($status === 'current', function ($query): void {
                $query->whereNotIn('status', ['Completed', 'Cancelled']);
            })
            ->when($status === 'past', function ($query): void {
                $query->whereIn('status', ['Completed', 'Cancelled']);
            })
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_mobile', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('full_address', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForCustomer(Customer $customer, int $id): Order
    {
        $order = Order::query()
            ->with(['store', 'paymentMethod', 'rider', 'items'])
            ->whereBelongsTo($customer)
            ->find($id);

        if (! $order) {
            throw new OrderNotFoundException;
        }

        return $order;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Order $order, array $attributes): Order
    {
        $order->update($attributes);

        return $order->refresh()->load(['store', 'customer', 'paymentMethod', 'coupon', 'rider', 'items']);
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}
