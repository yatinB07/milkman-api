<?php

namespace App\Actions\Store\Orders;

use App\Data\Store\StoreOrderDecisionData;
use App\Models\Order;
use App\Models\Store;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class DecideStoreOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CustomerNotificationRepository $notifications,
    ) {}

    public function execute(Store $store, int $orderId, StoreOrderDecisionData $data): Order
    {
        return DB::transaction(function () use ($store, $orderId, $data): Order {
            $order = $this->orders->findForStore($store, $orderId);

            $order = $data->decision === 'accepted'
                ? $this->orders->markStoreAccepted($order)
                : $this->orders->markStoreRejected($order, (string) $data->rejectionComment);

            $this->recordCustomerNotification($order, $data);

            return $order;
        });
    }

    private function recordCustomerNotification(Order $order, StoreOrderDecisionData $data): void
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
                ? __('catalog.customer_order_confirmed_title')
                : (string) $data->rejectionComment,
            'description' => $accepted
                ? __('catalog.customer_order_confirmed_description', ['name' => $customerName, 'order' => $orderId])
                : __('catalog.customer_order_rejected_description', ['name' => $customerName, 'order' => $orderId]),
        ]);
    }
}
