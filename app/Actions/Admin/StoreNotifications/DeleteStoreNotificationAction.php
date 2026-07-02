<?php

namespace App\Actions\Admin\StoreNotifications;

use App\Repositories\StoreNotificationRepository;

class DeleteStoreNotificationAction
{
    public function __construct(
        private readonly StoreNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): void
    {
        $this->notifications->delete(
            $this->notifications->find($notificationId),
        );
    }
}
