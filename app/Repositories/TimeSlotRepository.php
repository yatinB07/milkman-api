<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreNotFoundException;
use App\Exceptions\Catalog\TimeSlotNotFoundException;
use App\Models\Store;
use App\Models\TimeSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TimeSlotRepository
{
    /** @return LengthAwarePaginator<int, TimeSlot> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return TimeSlot::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('starts_at', 'like', "%{$search}%")
                        ->orWhere('ends_at', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('starts_at')
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, TimeSlot> */
    public function paginateActiveForStore(int $storeId, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $this->activeStore($storeId);

        return TimeSlot::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('starts_at', 'like', "%{$search}%")
                        ->orWhere('ends_at', 'like', "%{$search}%");
                });
            })
            ->orderBy('starts_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): TimeSlot
    {
        return TimeSlot::query()->create($attributes)->load('store');
    }

    public function find(int $id): TimeSlot
    {
        $slot = TimeSlot::query()
            ->with('store')
            ->find($id);

        if (! $slot) {
            throw new TimeSlotNotFoundException;
        }

        return $slot;
    }

    /** @param array<string, mixed> $attributes */
    public function update(TimeSlot $slot, array $attributes): TimeSlot
    {
        $slot->update($attributes);

        return $slot->refresh()->load('store');
    }

    public function delete(TimeSlot $slot): void
    {
        $slot->delete();
    }

    private function activeStore(int $storeId): Store
    {
        $store = Store::query()
            ->where('is_active', true)
            ->find($storeId);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }
}
