<?php

namespace App\Actions\Admin\StoreNotifications;

use App\Data\Admin\ListQueryData;
use App\Repositories\StoreNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreNotificationsAction
{
    public function __construct(
        private readonly StoreNotificationRepository $notifications,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginate($query->search, $query->perPage);
    }
}
