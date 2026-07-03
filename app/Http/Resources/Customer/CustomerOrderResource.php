<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'customer_id' => $this->resource->getAttribute('customer_id'),
            'ordered_at' => $this->resource->getAttribute('ordered_at')?->toISOString(),
            'payment_method_id' => $this->resource->getAttribute('payment_method_id'),
            'address' => $this->resource->getAttribute('address'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'delivery_charge' => $this->resource->getAttribute('delivery_charge'),
            'coupon_id' => $this->resource->getAttribute('coupon_id'),
            'coupon_amount' => $this->resource->getAttribute('coupon_amount'),
            'total' => $this->resource->getAttribute('total'),
            'subtotal' => $this->resource->getAttribute('subtotal'),
            'transaction_id' => $this->resource->getAttribute('transaction_id'),
            'wallet_amount' => $this->resource->getAttribute('wallet_amount'),
            'customer_name' => $this->resource->getAttribute('customer_name'),
            'customer_mobile' => $this->resource->getAttribute('customer_mobile'),
            'status' => $this->resource->getAttribute('status'),
            'time_slot' => $this->resource->getAttribute('time_slot'),
            'order_type' => $this->resource->getAttribute('order_type'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
            ] : null),
            'payment_method' => $this->whenLoaded('paymentMethod', fn (): ?array => $this->resource->getRelation('paymentMethod') ? [
                'id' => $this->resource->getRelation('paymentMethod')->getKey(),
                'title' => $this->resource->getRelation('paymentMethod')->getAttribute('title'),
            ] : null),
            'items' => $this->whenLoaded('items', fn (): array => $this->resource->getRelation('items')->map(fn ($item): array => [
                'id' => $item->getKey(),
                'quantity' => $item->getAttribute('quantity'),
                'product_title' => $item->getAttribute('product_title'),
                'discount' => $item->getAttribute('discount'),
                'image_path' => $item->getAttribute('image_path'),
                'price' => $item->getAttribute('price'),
                'variant_title' => $item->getAttribute('variant_title'),
            ])->values()->all()),
        ];
    }
}
