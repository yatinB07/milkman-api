<?php

namespace App\Actions\Admin\RiderNotifications;

use App\Data\Admin\RiderNotificationData;
use App\Models\RiderNotification;
use App\Repositories\RiderNotificationRepository;

class UpdateRiderNotificationAction
{
    public function __construct(
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId, RiderNotificationData $data): RiderNotification
    {
        return $this->notifications->update(
            $this->notifications->find($notificationId),
            $data->toArray(),
        );
    }
}
