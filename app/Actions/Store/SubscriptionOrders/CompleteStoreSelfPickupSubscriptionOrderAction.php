<?php

namespace App\Actions\Store\SubscriptionOrders;

use App\Exceptions\Catalog\SelfPickupOrderRequiredException;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Support\Facades\DB;

class CompleteStoreSelfPickupSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId): SubscriptionOrder
    {
        return DB::transaction(function () use ($store, $orderId): SubscriptionOrder {
            $order = $this->orders->findForStore($store, $orderId);

            if ($order->getAttribute('order_type') !== 'Self Pickup') {
                throw new SelfPickupOrderRequiredException;
            }

            $order = $this->orders->markStoreSelfPickupCompleted($order);
            $this->recordCustomerNotification($order);

            return $order;
        });
    }

    private function recordCustomerNotification(SubscriptionOrder $order): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $this->notifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => __('catalog.customer_subscription_order_completed_title_legacy'),
            'description' => __('catalog.customer_subscription_order_completed_description_legacy', [
                'name' => (string) ($order->getAttribute('customer_name') ?: __('catalog.customer')),
                'order' => $order->getKey(),
            ]),
        ]);
    }
}
