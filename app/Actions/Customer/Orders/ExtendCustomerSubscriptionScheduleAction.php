<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\SubscriptionScheduleChangeData;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderItemRepository;
use App\Repositories\SubscriptionOrderRepository;
use App\Services\SubscriptionScheduleService;

class ExtendCustomerSubscriptionScheduleAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly SubscriptionOrderItemRepository $items,
        private readonly SubscriptionScheduleService $schedules,
    ) {}

    public function execute(Customer $customer, int $orderId, int $itemId, SubscriptionScheduleChangeData $data): SubscriptionOrder
    {
        $item = $this->items->findForCustomerSubscriptionOrder($customer, $orderId, $itemId);

        $this->items->update($item, [
            'total_dates' => $this->schedules->extendedDates(
                (string) $item->getAttribute('total_dates'),
                (string) $item->getAttribute('selected_days'),
                $data->dates,
            ),
        ]);

        return $this->orders->findForCustomer($customer, $orderId);
    }
}
