<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ProductVariantNotFoundException;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductVariantRepository
{
    /** @return LengthAwarePaginator<int, ProductVariant> */
    public function paginate(?string $search = null, int $perPage = 15, ?bool $isOutOfStock = null): LengthAwarePaginator
    {
        return ProductVariant::query()
            ->with(['store', 'product'])
            ->when($isOutOfStock !== null, fn ($query) => $query->where('is_out_of_stock', $isOutOfStock))
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, ProductVariant> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return ProductVariant::query()
            ->with('product')
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): ProductVariant
    {
        return ProductVariant::query()
            ->create(array_merge($attributes, ['store_id' => $store->getKey()]))
            ->load('product');
    }

    public function findForStore(Store $store, int $id): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with('product')
            ->whereBelongsTo($store)
            ->find($id);

        if (! $variant) {
            throw new ProductVariantNotFoundException;
        }

        return $variant;
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): ProductVariant
    {
        return ProductVariant::query()->create($attributes)->load(['store', 'product']);
    }

    public function find(int $id): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with(['store', 'product'])
            ->find($id);

        if (! $variant) {
            throw new ProductVariantNotFoundException;
        }

        return $variant;
    }

    /** @param array<string, mixed> $attributes */
    public function update(ProductVariant $variant, array $attributes): ProductVariant
    {
        $variant->update($attributes);

        return $variant->refresh()->load(['store', 'product']);
    }

    public function delete(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
