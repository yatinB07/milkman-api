<?php

namespace App\Repositories;

use App\Exceptions\Catalog\ZoneNotFoundException;
use App\Models\Zone;
use App\Support\Zones\ZoneGeometry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ZoneRepository
{
    public function __construct(
        private readonly ZoneGeometry $geometry,
    ) {}

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
        return Zone::query()->create($this->prepareSpatialAttributes($attributes));
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
        $zone->update($this->prepareSpatialAttributes($attributes));

        return $zone->refresh();
    }

    public function delete(Zone $zone): void
    {
        $zone->delete();
    }

    public function findActiveContainingPoint(float $lat, float $lng): ?Zone
    {
        return Zone::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->first(fn (Zone $zone): bool => $this->geometry->containsPoint(
                (string) $zone->getAttribute('coordinates'),
                $lat,
                $lng,
            ));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareSpatialAttributes(array $attributes): array
    {
        if (! array_key_exists('coordinates', $attributes) || ! is_string($attributes['coordinates'])) {
            return $attributes;
        }

        $coordinates = $attributes['coordinates'];
        $normalized = $this->geometry->normalizePolygon($coordinates);

        if ($normalized !== null) {
            $attributes['coordinates'] = $normalized;
        }

        $alias = $attributes['alias'] ?? null;

        if (($alias === null || $alias === '') && $this->geometry->isMapAlias($coordinates)) {
            $attributes['alias'] = $coordinates;
        }

        return $attributes;
    }
}
