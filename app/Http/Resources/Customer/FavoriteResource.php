<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'zone_id' => $this->resource->getAttribute('zone_id'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
                'image_path' => $this->resource->getRelation('store')->getAttribute('image_path'),
                'rating' => $this->resource->getRelation('store')->getAttribute('rating'),
                'full_address' => $this->resource->getRelation('store')->getAttribute('full_address'),
            ] : null),
            'zone' => $this->whenLoaded('zone', fn (): ?array => $this->resource->getRelation('zone') ? [
                'id' => $this->resource->getRelation('zone')->getKey(),
                'title' => $this->resource->getRelation('zone')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
