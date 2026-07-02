<?php

namespace App\Actions\Admin\StoreNotifications;

use App\Data\Admin\StoreNotificationData;
use App\Models\StoreNotification;
use App\Repositories\StoreNotificationRepository;

class CreateStoreNotificationAction
{
    public function __construct(
        private readonly StoreNotificationRepository $notifications,
    ) {}

    public function execute(StoreNotificationData $data): StoreNotification
    {
        return $this->notifications->create($data->toArray());
    }
}
