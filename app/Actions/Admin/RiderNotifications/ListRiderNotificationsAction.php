<?php

namespace App\Actions\Admin\RiderNotifications;

use App\Data\Admin\ListQueryData;
use App\Repositories\RiderNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRiderNotificationsAction
{
    public function __construct(
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginate($query->search, $query->perPage);
    }
}
