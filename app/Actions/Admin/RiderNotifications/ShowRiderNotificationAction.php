<?php

namespace App\Actions\Admin\RiderNotifications;

use App\Models\RiderNotification;
use App\Repositories\RiderNotificationRepository;

class ShowRiderNotificationAction
{
    public function __construct(
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): RiderNotification
    {
        return $this->notifications->find($notificationId);
    }
}
