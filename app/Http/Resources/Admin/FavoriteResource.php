<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'customer_id' => $this->resource->getAttribute('customer_id'),
            'store_id' => $this->resource->getAttribute('store_id'),
            'zone_id' => $this->resource->getAttribute('zone_id'),
            'customer' => $this->whenLoaded('customer', fn (): ?array => $this->resource->getRelation('customer') ? [
                'id' => $this->resource->getRelation('customer')->getKey(),
                'name' => $this->resource->getRelation('customer')->getAttribute('name'),
                'email' => $this->resource->getRelation('customer')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('customer')->getAttribute('mobile'),
            ] : null),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
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
