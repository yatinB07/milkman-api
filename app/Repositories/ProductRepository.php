<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ProductNotFoundException;
use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    /** @return LengthAwarePaginator<int, Product> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['store', 'storeCategory'])
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('storeCategory', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, Product> */
    public function paginateActiveForStore(int $storeId, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $this->activeStore($storeId);

        return Product::query()
            ->with([
                'storeCategory',
                'variants' => fn ($query) => $query->orderBy('id'),
                'images' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
            ])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->whereHas('variants')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function findActiveForCustomer(int $id): Product
    {
        $product = Product::query()
            ->with([
                'store',
                'storeCategory',
                'variants' => fn ($query) => $query->orderBy('id'),
                'images' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
            ])
            ->where('is_active', true)
            ->whereHas('store', fn ($query) => $query->where('is_active', true))
            ->whereHas('variants')
            ->find($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        return $product;
    }

    /** @return LengthAwarePaginator<int, Product> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with('storeCategory')
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('storeCategory', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): Product
    {
        return Product::query()
            ->create(array_merge($attributes, ['store_id' => $store->getKey()]))
            ->load('storeCategory');
    }

    public function findForStore(Store $store, int $id): Product
    {
        $product = Product::query()
            ->with('storeCategory')
            ->whereBelongsTo($store)
            ->find($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        return $product;
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Product
    {
        return Product::query()->create($attributes)->load(['store', 'storeCategory']);
    }

    public function find(int $id): Product
    {
        $product = Product::query()
            ->with(['store', 'storeCategory'])
            ->find($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        return $product;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Product $product, array $attributes): Product
    {
        $product->update($attributes);

        return $product->refresh()->load(['store', 'storeCategory']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
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
