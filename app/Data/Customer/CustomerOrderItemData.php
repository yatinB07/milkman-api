<?php

namespace App\Data\Customer;

final readonly class CustomerOrderItemData
{
    public function __construct(
        public int $quantity,
        public string $productTitle,
        public float $discount,
        public ?string $imagePath,
        public float $price,
        public string $variantTitle,
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
        );
    }

    /** @return array<string, mixed> */
    public function toOrderItemAttributes(): array
    {
        return [
            'quantity' => $this->quantity,
            'product_title' => $this->productTitle,
            'discount' => $this->discount,
            'image_path' => $this->imagePath,
            'price' => $this->price,
            'variant_title' => $this->variantTitle,
        ];
    }
}
