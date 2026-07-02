<?php

namespace App\Repositories;

use App\Exceptions\Catalog\FavoriteNotFoundException;
use App\Models\Favorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteRepository
{
    /** @return LengthAwarePaginator<int, Favorite> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Favorite::query()
            ->with(['customer', 'store', 'zone'])
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('customer', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('zone', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Favorite
    {
        return Favorite::query()->create($attributes)->load(['customer', 'store', 'zone']);
    }

    public function find(int $id): Favorite
    {
        $favorite = Favorite::query()
            ->with(['customer', 'store', 'zone'])
            ->find($id);

        if (! $favorite) {
            throw new FavoriteNotFoundException;
        }

        return $favorite;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Favorite $favorite, array $attributes): Favorite
    {
        $favorite->update($attributes);

        return $favorite->refresh()->load(['customer', 'store', 'zone']);
    }

    public function delete(Favorite $favorite): void
    {
        $favorite->delete();
    }
}
