<?php

namespace App\Actions\Rider\Notifications;

use App\Models\Rider;
use App\Models\RiderNotification;
use App\Repositories\RiderNotificationRepository;

class ShowRiderNotificationAction
{
    public function __construct(private readonly RiderNotificationRepository $notifications) {}

    public function execute(Rider $rider, int $notificationId): RiderNotification
    {
        return $this->notifications->findForRider($rider, $notificationId);
    }
}
