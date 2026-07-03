<?php

namespace App\Actions\Store\Notifications;

use App\Models\Store;
use App\Models\StoreNotification;
use App\Repositories\StoreNotificationRepository;

class ShowStoreNotificationAction
{
    public function __construct(private readonly StoreNotificationRepository $notifications) {}

    public function execute(Store $store, int $notificationId): StoreNotification
    {
        return $this->notifications->findForStore($store, $notificationId);
    }
}
