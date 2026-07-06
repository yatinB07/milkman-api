<?php

namespace App\Actions\Rider\SubscriptionOrders;

use App\Data\Rider\RiderOrderDecisionData;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\StoreNotificationRepository;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Support\Facades\DB;

class DecideRiderSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly CustomerNotificationRepository $customerNotifications,
        private readonly StoreNotificationRepository $storeNotifications,
    ) {}

    public function execute(Rider $rider, int $orderId, RiderOrderDecisionData $data): SubscriptionOrder
    {
        return DB::transaction(function () use ($rider, $orderId, $data): SubscriptionOrder {
            $order = $this->orders->findForRider($rider, $orderId);

            $order = $data->decision === 'accepted'
                ? $this->orders->markRiderAccepted($order)
                : $this->orders->markRiderRejected($order, (string) $data->rejectionComment);

            $this->recordNotifications($order, $data);

            return $order;
        });
    }

    private function recordNotifications(SubscriptionOrder $order, RiderOrderDecisionData $data): void
    {
        if ($data->decision === 'accepted') {
            $this->recordCustomerOnRouteNotification($order);
            $this->recordStoreNotification(
                $order,
                __('catalog.store_rider_subscription_order_accepted_title'),
                __('catalog.store_rider_subscription_order_accepted_description', ['order' => $order->getKey()]),
            );

            return;
        }

        $this->recordStoreNotification(
            $order,
            (string) $data->rejectionComment,
            __('catalog.store_rider_order_rejected_description'),
        );
    }

    private function recordCustomerOnRouteNotification(SubscriptionOrder $order): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $this->customerNotifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => __('catalog.customer_subscription_order_on_route_title'),
            'description' => __('catalog.customer_subscription_order_on_route_description', [
                'name' => (string) ($order->getAttribute('customer_name') ?: __('catalog.customer')),
                'order' => $order->getKey(),
            ]),
        ]);
    }

    private function recordStoreNotification(SubscriptionOrder $order, string $title, string $description): void
    {
        $storeId = $order->getAttribute('store_id');

        if (! $storeId) {
            return;
        }

        $this->storeNotifications->create([
            'store_id' => $storeId,
            'notified_at' => now(),
            'title' => $title,
            'description' => $description,
        ]);
    }
}
