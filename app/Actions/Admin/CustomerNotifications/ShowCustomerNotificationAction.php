<?php

namespace App\Actions\Admin\CustomerNotifications;

use App\Models\CustomerNotification;
use App\Repositories\CustomerNotificationRepository;

class ShowCustomerNotificationAction
{
    public function __construct(
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): CustomerNotification
    {
        return $this->notifications->find($notificationId);
    }
}
