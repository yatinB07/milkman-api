<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;

class CatalogRepository
{
    /** @return Collection<int, Category> */
    public function activeCategories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    /** @return Collection<int, Store> */
    public function activeStores(?string $search = null): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhere('category_reference', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->get();
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

    /** @return Collection<int, Product> */
    public function activeStoreProducts(int $storeId): Collection
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
            ->orderBy('title')
            ->get();
    }
}
