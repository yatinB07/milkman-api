<?php

namespace App\Actions\Rider\Orders;

use App\Data\Rider\RiderOrderCompletionData;
use App\Exceptions\Catalog\DeliveryOrderRequiredException;
use App\Exceptions\Catalog\InvalidSignatureImageException;
use App\Models\Order;
use App\Models\Rider;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StoreNotificationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompleteRiderOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CustomerNotificationRepository $customerNotifications,
        private readonly StoreNotificationRepository $storeNotifications,
    ) {}

    public function execute(Rider $rider, int $orderId, RiderOrderCompletionData $data): Order
    {
        return DB::transaction(function () use ($rider, $orderId, $data): Order {
            $order = $this->orders->findForRider($rider, $orderId);

            if ($order->getAttribute('order_type') !== 'Delivery') {
                throw new DeliveryOrderRequiredException;
            }

            $signaturePath = $this->storeSignature($data->signatureImage);
            $order = $this->orders->markRiderCompleted($order, $signaturePath);
            $this->recordCustomerNotification($order);
            $this->recordStoreNotification($order);

            return $order;
        });
    }

    private function storeSignature(string $signatureImage): string
    {
        $payload = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $signatureImage) ?? '';
        $payload = str_replace(' ', '+', $payload);
        $contents = base64_decode($payload, true);

        if ($contents === false) {
            throw new InvalidSignatureImageException;
        }

        $path = 'signatures/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function recordCustomerNotification(Order $order): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $this->customerNotifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => __('catalog.customer_order_completed_legacy_title'),
            'description' => __('catalog.customer_order_completed_legacy_description', [
                'name' => (string) ($order->getAttribute('customer_name') ?: __('catalog.customer')),
                'order' => $order->getKey(),
            ]),
        ]);
    }

    private function recordStoreNotification(Order $order): void
    {
        $storeId = $order->getAttribute('store_id');

        if (! $storeId) {
            return;
        }

        $this->storeNotifications->create([
            'store_id' => $storeId,
            'notified_at' => now(),
            'title' => __('catalog.store_order_completed_title'),
            'description' => __('catalog.store_order_completed_description', ['order' => $order->getKey()]),
        ]);
    }
}
