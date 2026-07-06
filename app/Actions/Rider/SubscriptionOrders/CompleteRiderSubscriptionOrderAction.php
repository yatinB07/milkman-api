<?php

namespace App\Actions\Rider\SubscriptionOrders;

use App\Data\Rider\RiderOrderCompletionData;
use App\Exceptions\Catalog\IncompleteSubscriptionDeliveryDatesException;
use App\Exceptions\Catalog\InvalidSignatureImageException;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Repositories\CustomerNotificationRepository;
use App\Repositories\StoreNotificationRepository;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompleteRiderSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $orders,
        private readonly CustomerNotificationRepository $customerNotifications,
        private readonly StoreNotificationRepository $storeNotifications,
    ) {}

    public function execute(Rider $rider, int $orderId, RiderOrderCompletionData $data): SubscriptionOrder
    {
        return DB::transaction(function () use ($rider, $orderId, $data): SubscriptionOrder {
            $order = $this->orders->findForRider($rider, $orderId);

            if (! $this->allDeliveryDatesCompleted($order)) {
                throw new IncompleteSubscriptionDeliveryDatesException;
            }

            $signaturePath = $this->storeSignature($data->signatureImage);
            $order = $this->orders->markRiderCompleted($order, $signaturePath);
            $this->recordCustomerNotification($order);
            $this->recordStoreNotification($order);

            return $order;
        });
    }

    private function allDeliveryDatesCompleted(SubscriptionOrder $order): bool
    {
        foreach ($order->getRelation('items') as $item) {
            if (count($this->dates((string) $item->getAttribute('total_dates'))) !== count($this->dates((string) $item->getAttribute('completed_dates')))) {
                return false;
            }
        }

        return true;
    }

    /** @return list<string> */
    private function dates(string $dates): array
    {
        if ($dates === '' || $dates === '[]') {
            return [];
        }

        return collect(explode(',', $dates))
            ->map(fn (string $date): string => trim($date, " \t\n\r\0\x0B\"[]"))
            ->filter()
            ->values()
            ->all();
    }

    private function storeSignature(string $signatureImage): string
    {
        $payload = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $signatureImage) ?? '';
        $payload = str_replace(' ', '+', $payload);
        $contents = base64_decode($payload, true);

        if ($contents === false) {
            throw new InvalidSignatureImageException;
        }

        $path = 'subscription-signatures/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function recordCustomerNotification(SubscriptionOrder $order): void
    {
        $customerId = $order->getAttribute('customer_id');

        if (! $customerId) {
            return;
        }

        $this->customerNotifications->create([
            'customer_id' => $customerId,
            'notified_at' => now(),
            'title' => __('catalog.customer_subscription_order_completed_title_legacy'),
            'description' => __('catalog.customer_subscription_order_completed_description_legacy', [
                'name' => (string) ($order->getAttribute('customer_name') ?: __('catalog.customer')),
                'order' => $order->getKey(),
            ]),
        ]);
    }

    private function recordStoreNotification(SubscriptionOrder $order): void
    {
        $storeId = $order->getAttribute('store_id');

        if (! $storeId) {
            return;
        }

        $this->storeNotifications->create([
            'store_id' => $storeId,
            'notified_at' => now(),
            'title' => __('catalog.store_subscription_order_completed_title'),
            'description' => __('catalog.store_subscription_order_completed_description', ['order' => $order->getKey()]),
        ]);
    }
}
