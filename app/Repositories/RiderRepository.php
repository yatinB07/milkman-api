<?php

namespace App\Repositories;

use App\Exceptions\Catalog\RiderNotFoundException;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiderRepository
{
    /** @return LengthAwarePaginator<int, Rider> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Rider::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Rider
    {
        return Rider::query()->create($attributes)->load('store');
    }

    public function find(int $id): Rider
    {
        $rider = Rider::query()
            ->with('store')
            ->find($id);

        if (! $rider) {
            throw new RiderNotFoundException;
        }

        return $rider;
    }

    /** @return LengthAwarePaginator<int, Rider> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Rider::query()
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): Rider
    {
        return Rider::query()
            ->create(array_merge($attributes, [
                'store_id' => $store->getKey(),
                'joined_at' => now()->toDateString(),
            ]));
    }

    public function findForStore(Store $store, int $id): Rider
    {
        $rider = Rider::query()
            ->whereBelongsTo($store)
            ->find($id);

        if (! $rider) {
            throw new RiderNotFoundException;
        }

        return $rider;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Rider $rider, array $attributes): Rider
    {
        $rider->update($attributes);

        return $rider->refresh()->load('store');
    }

    public function delete(Rider $rider): void
    {
        $rider->delete();
    }
}
