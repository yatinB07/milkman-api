<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'store_category_id' => $this->resource->getAttribute('store_category_id'),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'description' => $this->resource->getAttribute('description'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'store_category' => $this->whenLoaded('storeCategory', fn (): ?array => $this->resource->getRelation('storeCategory') ? [
                'id' => $this->resource->getRelation('storeCategory')->getKey(),
                'title' => $this->resource->getRelation('storeCategory')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
