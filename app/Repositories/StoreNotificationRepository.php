<?php

namespace App\Repositories;

use App\Exceptions\Catalog\StoreNotificationNotFoundException;
use App\Models\StoreNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoreNotificationRepository
{
    /** @return LengthAwarePaginator<int, StoreNotification> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return StoreNotification::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('notified_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): StoreNotification
    {
        return StoreNotification::query()->create($attributes)->load('store');
    }

    public function find(int $id): StoreNotification
    {
        $notification = StoreNotification::query()
            ->with('store')
            ->find($id);

        if (! $notification) {
            throw new StoreNotificationNotFoundException;
        }

        return $notification;
    }

    /** @param array<string, mixed> $attributes */
    public function update(StoreNotification $notification, array $attributes): StoreNotification
    {
        $notification->update($attributes);

        return $notification->refresh()->load('store');
    }

    public function delete(StoreNotification $notification): void
    {
        $notification->delete();
    }
}
