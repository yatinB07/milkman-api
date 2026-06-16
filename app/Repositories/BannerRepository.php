<?php

namespace App\Repositories;

use App\Exceptions\Catalog\BannerNotFoundException;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;

class BannerRepository
{
    /** @return Collection<int, Banner> */
    public function all(): Collection
    {
        return Banner::query()
            ->orderByDesc('id')
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
