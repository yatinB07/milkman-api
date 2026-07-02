<?php

namespace App\Actions\Admin\CustomerNotifications;

use App\Data\Admin\CustomerNotificationData;
use App\Models\CustomerNotification;
use App\Repositories\CustomerNotificationRepository;

class UpdateCustomerNotificationAction
{
    public function __construct(
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId, CustomerNotificationData $data): CustomerNotification
    {
        return $this->notifications->update(
            $this->notifications->find($notificationId),
            $data->toArray(),
        );
    }
}
