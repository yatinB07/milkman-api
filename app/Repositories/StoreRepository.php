<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
    /** @return LengthAwarePaginator<int, Store> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Store::query()
            ->with('zone')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhereHas('zone', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @return Collection<int, Store> */
    public function activeForHome(int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Store> */
    public function topRatedForHome(int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Store> */
    public function favoriteStoresForHome(Customer $customer, int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->whereHas('favorites', function ($query) use ($customer): void {
                $query->where('customer_id', $customer->getKey());
            })
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Store
    {
        return Store::query()->create($attributes)->load('zone');
    }

    public function find(int $id): Store
    {
        $store = Store::query()
            ->with('zone')
            ->find($id);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Store $store, array $attributes): Store
    {
        $store->update($attributes);

        return $store->refresh()->load('zone');
    }

    public function delete(Store $store): void
    {
        $store->delete();
    }
}
