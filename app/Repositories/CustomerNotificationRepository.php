<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CustomerNotificationNotFoundException;
use App\Models\CustomerNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerNotificationRepository
{
    /** @return LengthAwarePaginator<int, CustomerNotification> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return CustomerNotification::query()
            ->with('customer')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search): void {
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
    public function create(array $attributes): CustomerNotification
    {
        return CustomerNotification::query()->create($attributes)->load('customer');
    }

    public function find(int $id): CustomerNotification
    {
        $notification = CustomerNotification::query()
            ->with('customer')
            ->find($id);

        if (! $notification) {
            throw new CustomerNotificationNotFoundException;
        }

        return $notification;
    }

    /** @param array<string, mixed> $attributes */
    public function update(CustomerNotification $notification, array $attributes): CustomerNotification
    {
        $notification->update($attributes);

        return $notification->refresh()->load('customer');
    }

    public function delete(CustomerNotification $notification): void
    {
        $notification->delete();
    }
}
