<?php

namespace App\Data\Customer;

final readonly class CustomerSubscriptionOrderData
{
    /** @param array<int, CustomerSubscriptionOrderItemData> $items */
    public function __construct(
        public int $storeId,
        public int $paymentMethodId,
        public string $address,
        public ?string $landmark,
        public float $deliveryCharge,
        public ?int $couponId,
        public float $couponAmount,
        public float $total,
        public float $subtotal,
        public string $transactionId,
        public ?string $adminNote,
        public float $walletAmount,
        public string $customerName,
        public string $customerMobile,
        public string $orderType,
        public array $items,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            storeId: (int) $data['store_id'],
            paymentMethodId: (int) $data['payment_method_id'],
            address: (string) $data['address'],
            landmark: $data['landmark'] ?? null,
            deliveryCharge: (float) $data['delivery_charge'],
            couponId: isset($data['coupon_id']) ? (int) $data['coupon_id'] : null,
            couponAmount: (float) ($data['coupon_amount'] ?? 0),
            total: (float) $data['total'],
            subtotal: (float) $data['subtotal'],
            transactionId: (string) $data['transaction_id'],
            adminNote: $data['admin_note'] ?? null,
            walletAmount: (float) ($data['wallet_amount'] ?? 0),
            customerName: (string) $data['customer_name'],
            customerMobile: (string) $data['customer_mobile'],
            orderType: (string) ($data['order_type'] ?? 'Subscription'),
            items: array_map(
                fn (array $item): CustomerSubscriptionOrderItemData => CustomerSubscriptionOrderItemData::fromArray($item),
                $data['items'],
            ),
        );
    }
}
