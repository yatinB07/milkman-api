<?php

namespace App\Repositories;

use App\Data\Catalog\PublicListQueryData;
use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CatalogRepository
{
    /** @return LengthAwarePaginator<int, Category> */
    public function activeCategories(PublicListQueryData $query): LengthAwarePaginator
    {
        return Category::query()
            ->where('is_active', true)
            ->when($query->search, function ($builder, string $search): void {
                $builder->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate($query->perPage);
    }

    /** @return LengthAwarePaginator<int, Store> */
    public function activeStores(PublicListQueryData $query): LengthAwarePaginator
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->when($query->search, function ($builder, string $search): void {
                $builder->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhere('category_reference', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($query->perPage);
    }

    public function activeStoreDetail(int $storeId): Store
    {
        $store = Store::query()
            ->with([
                'zone',
                'galleryImages' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
                'deliveryOptions' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
                'timeSlots' => fn ($query) => $query->where('is_active', true)->orderBy('starts_at'),
                'coupons' => fn ($query) => $query->where('is_active', true)->orderBy('title'),
                'faqs' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
            ])
            ->where('is_active', true)
            ->find($storeId);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }

    /** @return LengthAwarePaginator<int, Product> */
    public function activeStoreProducts(int $storeId, PublicListQueryData $query): LengthAwarePaginator
    {
        $this->activeStoreDetail($storeId);

        return Product::query()
            ->with([
                'variants' => fn ($query) => $query->where('is_out_of_stock', false)->orderBy('id'),
                'images' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
                'storeCategory',
            ])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->when($query->search, function ($builder, string $search): void {
                $builder->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($query->perPage);
    }
}
