<?php

namespace App\Actions\Admin\CustomerNotifications;

use App\Repositories\CustomerNotificationRepository;

class DeleteCustomerNotificationAction
{
    public function __construct(
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(int $notificationId): void
    {
        $this->notifications->delete(
            $this->notifications->find($notificationId),
        );
    }
}
