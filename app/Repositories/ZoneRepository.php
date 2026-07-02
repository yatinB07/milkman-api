<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ZoneNotFoundException;
use App\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ZoneRepository
{
    /** @return LengthAwarePaginator<int, Zone> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Zone::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('alias', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Zone
    {
        return Zone::query()->create($attributes);
    }

    public function find(int $id): Zone
    {
        $zone = Zone::query()->find($id);

        if (! $zone) {
            throw new ZoneNotFoundException;
        }

        return $zone;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Zone $zone, array $attributes): Zone
    {
        $zone->update($attributes);

        return $zone->refresh();
    }

    public function delete(Zone $zone): void
    {
        $zone->delete();
    }
}
