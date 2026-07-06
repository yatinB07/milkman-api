<?php

namespace App\Repositories;

use App\Exceptions\Catalog\RiderNotificationNotFoundException;
use App\Models\Rider;
use App\Models\RiderNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiderNotificationRepository
{
    /** @return LengthAwarePaginator<int, RiderNotification> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return RiderNotification::query()
            ->with('rider')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('rider', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('notified_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): RiderNotification
    {
        return RiderNotification::query()->create($attributes)->load('rider');
    }

    public function find(int $id): RiderNotification
    {
        $notification = RiderNotification::query()
            ->with('rider')
            ->find($id);

        if (! $notification) {
            throw new RiderNotificationNotFoundException;
        }

        return $notification;
    }

    /** @return LengthAwarePaginator<int, RiderNotification> */
    public function paginateForRider(Rider $rider, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return RiderNotification::query()
            ->whereBelongsTo($rider)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest('notified_at')
            ->paginate($perPage);
    }

    public function findForRider(Rider $rider, int $id): RiderNotification
    {
        $notification = RiderNotification::query()
            ->whereBelongsTo($rider)
            ->find($id);

        if (! $notification) {
            throw new RiderNotificationNotFoundException;
        }

        return $notification;
    }

    /** @param array<string, mixed> $attributes */
    public function update(RiderNotification $notification, array $attributes): RiderNotification
    {
        $notification->update($attributes);

        return $notification->refresh()->load('rider');
    }

    public function delete(RiderNotification $notification): void
    {
        $notification->delete();
    }
}
