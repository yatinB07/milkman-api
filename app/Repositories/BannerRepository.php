<?php

namespace App\Repositories;

use App\Exceptions\Catalog\BannerNotFoundException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BannerRepository
{
    /** @return LengthAwarePaginator<int, Banner> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Banner::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /** @return Collection<int, Banner> */
    public function activeForHome(int $limit): Collection
    {
        return Banner::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Banner
    {
        return Banner::query()->create($attributes);
    }

    public function find(int $id): Banner
    {
        $banner = Banner::query()->find($id);

        if (! $banner) {
            throw new BannerNotFoundException;
        }

        return $banner;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Banner $banner, array $attributes): Banner
    {
        $banner->update($attributes);

        return $banner->refresh();
    }

    public function delete(Banner $banner): void
    {
        $banner->delete();
    }
}
