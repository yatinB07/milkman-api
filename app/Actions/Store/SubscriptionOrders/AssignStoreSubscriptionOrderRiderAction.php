<?php

namespace App\Actions\Store\SubscriptionOrders;

use App\Data\Store\StoreOrderRiderAssignmentData;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Repositories\RiderNotificationRepository;
use App\Repositories\RiderRepository;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Support\Facades\DB;

class AssignStoreSubscriptionOrderRiderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly RiderRepository $riders,
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId, StoreOrderRiderAssignmentData $data): SubscriptionOrder
    {
        return DB::transaction(function () use ($store, $orderId, $data): SubscriptionOrder {
            $order = $this->orders->findForStore($store, $orderId);
            $rider = $this->riders->findForStore($store, $data->riderId);
            $order = $this->orders->assignRider($order, (int) $rider->getKey());

            $this->notifications->create([
                'rider_id' => $rider->getKey(),
                'notified_at' => now(),
                'title' => __('catalog.rider_subscription_order_assigned_title', ['order' => $order->getKey()]),
                'message' => __('catalog.rider_subscription_order_assigned_message'),
            ]);

            return $order;
        });
    }
}
