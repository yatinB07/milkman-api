<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ProductNotFoundException;
use App\Models\Product;
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
}
