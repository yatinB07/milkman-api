<?php

namespace App\Actions\Admin\RiderNotifications;

use App\Repositories\RiderNotificationRepository;

class DeleteRiderNotificationAction
{
    public function __construct(
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): void
    {
        $this->notifications->delete(
            $this->notifications->find($notificationId),
        );
    }
}
