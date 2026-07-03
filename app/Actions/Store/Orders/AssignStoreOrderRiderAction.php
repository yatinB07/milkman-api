<?php

namespace App\Actions\Store\Orders;

use App\Data\Store\StoreOrderRiderAssignmentData;
use App\Models\Order;
use App\Models\Store;
use App\Repositories\OrderRepository;
use App\Repositories\RiderNotificationRepository;
use App\Repositories\RiderRepository;
use Illuminate\Support\Facades\DB;

class AssignStoreOrderRiderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly RiderRepository $riders,
        private readonly RiderNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId, StoreOrderRiderAssignmentData $data): Order
    {
        return DB::transaction(function () use ($store, $orderId, $data): Order {
            $order = $this->orders->findForStore($store, $orderId);
            $rider = $this->riders->findForStore($store, $data->riderId);
            $order = $this->orders->assignRider($order, (int) $rider->getKey());

            $this->notifications->create([
                'rider_id' => $rider->getKey(),
                'notified_at' => now(),
                'title' => __('catalog.rider_order_assigned_title', ['order' => $order->getKey()]),
                'message' => __('catalog.rider_order_assigned_message'),
            ]);

            return $order;
        });
    }
}
