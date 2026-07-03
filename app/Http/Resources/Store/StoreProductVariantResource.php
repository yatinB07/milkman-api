<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductVariantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'product_id' => $this->resource->getAttribute('product_id'),
            'title' => $this->resource->getAttribute('title'),
            'subscribe_price' => $this->resource->getAttribute('subscribe_price'),
            'normal_price' => $this->resource->getAttribute('normal_price'),
            'discount' => $this->resource->getAttribute('discount'),
            'is_out_of_stock' => $this->resource->getAttribute('is_out_of_stock'),
            'is_subscription_required' => $this->resource->getAttribute('is_subscription_required'),
            'product' => $this->whenLoaded('product', fn (): ?array => $this->resource->getRelation('product') ? [
                'id' => $this->resource->getRelation('product')->getKey(),
                'title' => $this->resource->getRelation('product')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
