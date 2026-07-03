<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductImageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'product_id' => $this->resource->getAttribute('product_id'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'product' => $this->whenLoaded('product', fn (): ?array => $this->resource->getRelation('product') ? [
                'id' => $this->resource->getRelation('product')->getKey(),
                'title' => $this->resource->getRelation('product')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
