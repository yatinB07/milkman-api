<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ProductImageNotFoundException;
use App\Models\ProductImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductImageRepository
{
    /** @return LengthAwarePaginator<int, ProductImage> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return ProductImage::query()
            ->with(['store', 'product'])
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('image_path', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('image_path')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): ProductImage
    {
        return ProductImage::query()->create($attributes)->load(['store', 'product']);
    }

    public function find(int $id): ProductImage
    {
        $image = ProductImage::query()
            ->with(['store', 'product'])
            ->find($id);

        if (! $image) {
            throw new ProductImageNotFoundException;
        }

        return $image;
    }

    /** @param array<string, mixed> $attributes */
    public function update(ProductImage $image, array $attributes): ProductImage
    {
        $image->update($attributes);

        return $image->refresh()->load(['store', 'product']);
    }

    public function delete(ProductImage $image): void
    {
        $image->delete();
    }
}
