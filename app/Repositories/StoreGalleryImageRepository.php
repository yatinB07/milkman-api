<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreGalleryImageNotFoundException;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoreGalleryImageRepository
{
    /** @return LengthAwarePaginator<int, StoreGalleryImage> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return StoreGalleryImage::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('image_path', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('image_path')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): StoreGalleryImage
    {
        return StoreGalleryImage::query()->create($attributes)->load('store');
    }

    public function find(int $id): StoreGalleryImage
    {
        $image = StoreGalleryImage::query()
            ->with('store')
            ->find($id);

        if (! $image) {
            throw new StoreGalleryImageNotFoundException;
        }

        return $image;
    }

    /** @return LengthAwarePaginator<int, StoreGalleryImage> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return StoreGalleryImage::query()
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where('image_path', 'like', "%{$search}%");
            })
            ->orderBy('image_path')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): StoreGalleryImage
    {
        return StoreGalleryImage::query()
            ->create(array_merge($attributes, ['store_id' => $store->getKey()]));
    }

    public function findForStore(Store $store, int $id): StoreGalleryImage
    {
        $image = StoreGalleryImage::query()
            ->whereBelongsTo($store)
            ->find($id);

        if (! $image) {
            throw new StoreGalleryImageNotFoundException;
        }

        return $image;
    }

    /** @param array<string, mixed> $attributes */
    public function update(StoreGalleryImage $image, array $attributes): StoreGalleryImage
    {
        $image->update($attributes);

        return $image->refresh()->load('store');
    }

    public function delete(StoreGalleryImage $image): void
    {
        $image->delete();
    }
}
