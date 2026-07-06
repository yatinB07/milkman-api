<?php

namespace App\Repositories;

use App\Exceptions\Catalog\RiderNotFoundException;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiderRepository
{
    /** @return LengthAwarePaginator<int, Rider> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Rider::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Rider
    {
        return Rider::query()->create($attributes)->load('store');
    }

    public function find(int $id): Rider
    {
        $rider = Rider::query()
            ->with('store')
            ->find($id);

        if (! $rider) {
            throw new RiderNotFoundException;
        }

        return $rider;
    }

    /** @return LengthAwarePaginator<int, Rider> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Rider::query()
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): Rider
    {
        return Rider::query()
            ->create(array_merge($attributes, [
                'store_id' => $store->getKey(),
                'joined_at' => now()->toDateString(),
            ]));
    }

    public function findForStore(Store $store, int $id): Rider
    {
        $rider = Rider::query()
            ->whereBelongsTo($store)
            ->find($id);

        if (! $rider) {
            throw new RiderNotFoundException;
        }

        return $rider;
    }

    /** @return array<string, mixed> */
    public function dashboardMetrics(Rider $rider): array
    {
        $riderId = (int) $rider->getKey();
        $counts = [
            'normal_orders' => Order::query()->where('rider_id', $riderId)->count(),
            'completed_normal_orders' => Order::query()
                ->where('rider_id', $riderId)
                ->where('status', 'Completed')
                ->count(),
            'subscription_orders' => SubscriptionOrder::query()->where('rider_id', $riderId)->count(),
            'completed_subscription_orders' => SubscriptionOrder::query()
                ->where('rider_id', $riderId)
                ->where('status', 'Completed')
                ->count(),
        ];

        return [
            'counts' => $counts,
            'cards' => [
                [
                    'title' => __('catalog.rider_dashboard_normal_orders'),
                    'report_data' => $counts['normal_orders'],
                    'url' => 'images/dashboard/myorders.png',
                ],
                [
                    'title' => __('catalog.rider_dashboard_completed_orders'),
                    'report_data' => $counts['completed_normal_orders'],
                    'url' => 'images/dashboard/myorders.png',
                ],
                [
                    'title' => __('catalog.rider_dashboard_subscription_orders'),
                    'report_data' => $counts['subscription_orders'],
                    'url' => 'images/dashboard/myprescription.png',
                ],
                [
                    'title' => __('catalog.rider_dashboard_completed_orders'),
                    'report_data' => $counts['completed_subscription_orders'],
                    'url' => 'images/dashboard/myprescription.png',
                ],
            ],
            'withdraw_limit' => '0.00',
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function update(Rider $rider, array $attributes): Rider
    {
        $rider->update($attributes);

        return $rider->refresh()->load('store');
    }

    public function deactivateAccount(Rider $rider): Rider
    {
        $rider->update(['is_active' => false]);
        $rider->tokens()->delete();

        return $rider->refresh()->load('store');
    }

    public function delete(Rider $rider): void
    {
        $rider->delete();
    }
}
