<?php

namespace App\Actions\Rider\Notifications;

use App\Data\Rider\ListRiderQueryData;
use App\Models\Rider;
use App\Models\RiderNotification;
use App\Repositories\RiderNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRiderNotificationsAction
{
    public function __construct(private readonly RiderNotificationRepository $notifications) {}

    /** @return LengthAwarePaginator<int, RiderNotification> */
    public function execute(Rider $rider, ListRiderQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginateForRider($rider, $query->search, $query->perPage);
    }
}
