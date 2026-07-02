<?php

namespace App\Actions\Admin\CustomerNotifications;

use App\Data\Admin\ListQueryData;
use App\Repositories\CustomerNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerNotificationsAction
{
    public function __construct(
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginate($query->search, $query->perPage);
    }
}
