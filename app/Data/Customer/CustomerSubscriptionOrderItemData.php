<?php

namespace App\Data\Customer;

final readonly class CustomerSubscriptionOrderItemData
{
    /** @param array<int, int> $selectedDays */
    public function __construct(
        public int $quantity,
        public string $productTitle,
        public float $discount,
        public ?string $imagePath,
        public float $price,
        public string $variantTitle,
        public string $startsAt,
        public int $totalDeliveries,
        public array $selectedDays,
        public ?string $timeSlot,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            quantity: (int) $data['quantity'],
            productTitle: (string) $data['product_title'],
            discount: (float) ($data['discount'] ?? 0),
            imagePath: $data['image_path'] ?? null,
            price: (float) $data['price'],
            variantTitle: (string) $data['variant_title'],
            startsAt: (string) $data['starts_at'],
            totalDeliveries: (int) $data['total_deliveries'],
            selectedDays: array_map('intval', $data['selected_days']),
            timeSlot: $data['time_slot'] ?? null,
        );
    }
}
