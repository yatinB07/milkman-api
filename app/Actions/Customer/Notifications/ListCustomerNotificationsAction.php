<?php

namespace App\Actions\Customer\Notifications;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Customer;
use App\Repositories\CustomerNotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerNotificationsAction
{
    public function __construct(private readonly CustomerNotificationRepository $notifications) {}

    public function execute(Customer $customer, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->notifications->paginateForCustomer($customer, $query->search, $query->perPage);
    }
}
