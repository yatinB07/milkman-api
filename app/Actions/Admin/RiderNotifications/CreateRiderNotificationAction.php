<?php

namespace App\Actions\Admin\RiderNotifications;

use App\Data\Admin\RiderNotificationData;
use App\Models\RiderNotification;
use App\Repositories\RiderNotificationRepository;

class CreateRiderNotificationAction
{
    public function __construct(
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(RiderNotificationData $data): RiderNotification
    {
        return $this->notifications->create($data->toArray());
    }
}
