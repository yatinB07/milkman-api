<?php

namespace App\Actions\Store\Orders;

use App\Exceptions\Catalog\SelfPickupOrderRequiredException;
use App\Models\Order;
use App\Models\Store;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class CompleteStoreSelfPickupOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId): Order
    {
        return DB::transaction(function () use ($store, $orderId): Order {
            $order = $this->orders->findForStore($store, $orderId);

            if ($order->getAttribute('order_type') !== 'Self Pickup') {
                throw new SelfPickupOrderRequiredException;
            }

            $order = $this->orders->markSelfPickupCompleted($order);
            $this->recordCustomerNotification($order);

            return $order;
        });
    }

    private function recordCustomerNotification(Order $order): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $this->notifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => __('catalog.customer_order_completed_title'),
            'description' => __('catalog.customer_order_completed_description', [
                'name' => (string) ($order->getAttribute('customer_name') ?: __('catalog.customer')),
                'order' => $order->getKey(),
            ]),
        ]);
    }
}
