<?php

namespace App\Actions\Admin\CustomerNotifications;

use App\Data\Admin\CustomerNotificationData;
use App\Models\CustomerNotification;
use App\Repositories\CustomerNotificationRepository;

class CreateCustomerNotificationAction
{
    public function __construct(
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(CustomerNotificationData $data): CustomerNotification
    {
        return $this->notifications->create($data->toArray());
    }
}
