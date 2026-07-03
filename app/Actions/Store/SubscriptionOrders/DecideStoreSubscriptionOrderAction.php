<?php

namespace App\Actions\Store\SubscriptionOrders;

use App\Data\Store\StoreOrderDecisionData;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Support\Facades\DB;

class DecideStoreSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId, StoreOrderDecisionData $data): SubscriptionOrder
    {
        return DB::transaction(function () use ($store, $orderId, $data): SubscriptionOrder {
            $order = $this->orders->findForStore($store, $orderId);

            $order = $data->decision === 'accepted'
                ? $this->orders->markStoreAccepted($order)
                : $this->orders->markStoreRejected($order, (string) $data->rejectionComment);

            $this->recordCustomerNotification($order, $data);

            return $order;
        });
    }

    private function recordCustomerNotification(SubscriptionOrder $order, StoreOrderDecisionData $data): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $customerName = (string) ($order->getAttribute('customer_name') ?: __('catalog.customer'));
        $orderId = $order->getKey();
        $accepted = $data->decision === 'accepted';

        $this->notifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => $accepted
                ? __('catalog.customer_subscription_order_confirmed_title')
                : (string) $data->rejectionComment,
            'description' => $accepted
                ? __('catalog.customer_subscription_order_confirmed_description', ['name' => $customerName, 'order' => $orderId])
                : __('catalog.customer_subscription_order_rejected_description', ['name' => $customerName, 'order' => $orderId]),
        ]);
    }
}
