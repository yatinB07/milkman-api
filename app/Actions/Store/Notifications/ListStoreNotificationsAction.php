<?php

namespace App\Actions\Store\Notifications;

use App\Data\Store\ListStoreQueryData;
use App\Models\Store;
use App\Models\StoreNotification;
use App\Repositories\StoreNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreNotificationsAction
{
    public function __construct(private readonly StoreNotificationRepository $notifications) {}

    /** @return LengthAwarePaginator<int, StoreNotification> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginateForStore($store, $query->search, $query->perPage);
    }
}
