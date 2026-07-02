<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ProductVariantNotFoundException;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductVariantRepository
{
    /** @return LengthAwarePaginator<int, ProductVariant> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return ProductVariant::query()
            ->with(['store', 'product'])
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
