<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreCategoryNotFoundException;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoreCategoryRepository
{
    /** @return LengthAwarePaginator<int, StoreCategory> */
    public function paginate(?string $search = null, int $perPage = 15, ?bool $isActive = null): LengthAwarePaginator
    {
        return StoreCategory::query()
            ->with('store')
            ->when($isActive !== null, fn ($query) => $query->where('is_active', $isActive))
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

    /** @return LengthAwarePaginator<int, StoreCategory> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return StoreCategory::query()
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): StoreCategory
    {
        return StoreCategory::query()
            ->create(array_merge($attributes, ['store_id' => $store->getKey()]));
    }

    public function findForStore(Store $store, int $id): StoreCategory
    {
        $category = StoreCategory::query()
            ->whereBelongsTo($store)
            ->find($id);

        if (! $category) {
            throw new StoreCategoryNotFoundException;
        }

        return $category;
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): StoreCategory
    {
        return StoreCategory::query()->create($attributes)->load('store');
    }

    public function find(int $id): StoreCategory
    {
        $storeCategory = StoreCategory::query()
            ->with('store')
            ->find($id);

        if (! $storeCategory) {
            throw new StoreCategoryNotFoundException;
        }

        return $storeCategory;
    }

    /** @param array<string, mixed> $attributes */
    public function update(StoreCategory $storeCategory, array $attributes): StoreCategory
    {
        $storeCategory->update($attributes);

        return $storeCategory->refresh()->load('store');
    }

    public function delete(StoreCategory $storeCategory): void
    {
        $storeCategory->delete();
    }
}
