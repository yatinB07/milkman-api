<?php

namespace App\Actions\Admin\StoreNotifications;

use App\Models\StoreNotification;
use App\Repositories\StoreNotificationRepository;

class ShowStoreNotificationAction
{
    public function __construct(
        private readonly StoreNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): StoreNotification
    {
        return $this->notifications->find($notificationId);
    }
}
