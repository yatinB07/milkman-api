<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'order_id' => $this->resource->getAttribute('order_id'),
            'quantity' => $this->resource->getAttribute('quantity'),
            'product_title' => $this->resource->getAttribute('product_title'),
            'discount' => $this->resource->getAttribute('discount'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'price' => $this->resource->getAttribute('price'),
            'variant_title' => $this->resource->getAttribute('variant_title'),
            'order' => $this->whenLoaded('order', fn (): ?array => $this->resource->getRelation('order') ? [
                'id' => $this->resource->getRelation('order')->getKey(),
                'transaction_id' => $this->resource->getRelation('order')->getAttribute('transaction_id'),
                'customer_name' => $this->resource->getRelation('order')->getAttribute('customer_name'),
                'customer_mobile' => $this->resource->getRelation('order')->getAttribute('customer_mobile'),
                'status' => $this->resource->getRelation('order')->getAttribute('status'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
