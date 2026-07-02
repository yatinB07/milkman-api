<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'title' => $this->resource->getAttribute('title'),
            'code' => $this->resource->getAttribute('code'),
            'subtitle' => $this->resource->getAttribute('subtitle'),
            'expires_at' => $this->resource->getAttribute('expires_at')?->toDateString(),
            'minimum_amount' => $this->resource->getAttribute('minimum_amount'),
            'value' => $this->resource->getAttribute('value'),
            'description' => $this->resource->getAttribute('description'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
