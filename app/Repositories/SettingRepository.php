<?php

namespace App\Repositories;

use App\Exceptions\Catalog\SettingNotFoundException;
use App\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SettingRepository
{
    /** @return LengthAwarePaginator<int, Setting> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Setting::query()
            ->with('primaryStore')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('web_name', 'like', "%{$search}%")
                        ->orWhere('timezone', 'like', "%{$search}%")
                        ->orWhere('currency', 'like', "%{$search}%")
                        ->orWhere('sms_type', 'like', "%{$search}%")
                        ->orWhereHas('primaryStore', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Setting
    {
        return Setting::query()->create($attributes)->load('primaryStore');
    }

    public function find(int $id): Setting
    {
        $setting = Setting::query()
            ->with('primaryStore')
            ->find($id);

        if (! $setting) {
            throw new SettingNotFoundException;
        }

        return $setting;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Setting $setting, array $attributes): Setting
    {
        $setting->update($attributes);

        return $setting->refresh()->load('primaryStore');
    }

    public function delete(Setting $setting): void
    {
        $setting->delete();
    }
}
