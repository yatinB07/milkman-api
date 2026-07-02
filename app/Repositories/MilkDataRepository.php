<?php

namespace App\Repositories;

use App\Exceptions\Catalog\MilkDataNotFoundException;
use App\Models\MilkData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MilkDataRepository
{
    /** @return LengthAwarePaginator<int, MilkData> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return MilkData::query()
            ->when($search, function ($query, string $search): void {
                $query->where('data', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): MilkData
    {
        return MilkData::query()->create($attributes);
    }

    public function find(int $id): MilkData
    {
        $milkData = MilkData::query()->find($id);

        if (! $milkData) {
            throw new MilkDataNotFoundException;
        }

        return $milkData;
    }

    /** @param array<string, mixed> $attributes */
    public function update(MilkData $milkData, array $attributes): MilkData
    {
        $milkData->update($attributes);

        return $milkData->refresh();
    }

    public function delete(MilkData $milkData): void
    {
        $milkData->delete();
    }
}
