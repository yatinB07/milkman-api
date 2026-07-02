<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CashCollectionNotFoundException;
use App\Models\CashCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CashCollectionRepository
{
    /** @return LengthAwarePaginator<int, CashCollection> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return CashCollection::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('message', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('collected_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): CashCollection
    {
        return CashCollection::query()->create($attributes)->load('store');
    }

    public function find(int $id): CashCollection
    {
        $collection = CashCollection::query()
            ->with('store')
            ->find($id);

        if (! $collection) {
            throw new CashCollectionNotFoundException;
        }

        return $collection;
    }

    /** @param array<string, mixed> $attributes */
    public function update(CashCollection $collection, array $attributes): CashCollection
    {
        $collection->update($attributes);

        return $collection->refresh()->load('store');
    }

    public function delete(CashCollection $collection): void
    {
        $collection->delete();
    }
}
