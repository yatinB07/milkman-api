<?php

namespace App\Actions\Admin\StoreNotifications;

use App\Data\Admin\StoreNotificationData;
use App\Models\StoreNotification;
use App\Repositories\StoreNotificationRepository;

class UpdateStoreNotificationAction
{
    public function __construct(
        private readonly StoreNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId, StoreNotificationData $data): StoreNotification
    {
        return $this->notifications->update(
            $this->notifications->find($notificationId),
            $data->toArray(),
        );
    }
}
