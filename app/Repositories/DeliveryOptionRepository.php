<?php

namespace App\Repositories;

use App\Exceptions\Catalog\DeliveryOptionNotFoundException;
use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\DeliveryOption;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryOptionRepository
{
    /** @return LengthAwarePaginator<int, DeliveryOption> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return DeliveryOption::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, DeliveryOption> */
    public function paginateActiveForStore(int $storeId, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $this->activeStore($storeId);

        return DeliveryOption::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): DeliveryOption
    {
        return DeliveryOption::query()->create($attributes)->load('store');
    }

    public function find(int $id): DeliveryOption
    {
        $option = DeliveryOption::query()
            ->with('store')
            ->find($id);

        if (! $option) {
            throw new DeliveryOptionNotFoundException;
        }

        return $option;
    }

    /** @param array<string, mixed> $attributes */
    public function update(DeliveryOption $option, array $attributes): DeliveryOption
    {
        $option->update($attributes);

        return $option->refresh()->load('store');
    }

    public function delete(DeliveryOption $option): void
    {
        $option->delete();
    }

    private function activeStore(int $storeId): Store
    {
        $store = Store::query()
            ->where('is_active', true)
            ->find($storeId);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }
}
