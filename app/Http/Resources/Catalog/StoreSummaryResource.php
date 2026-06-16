<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'cover_image_path' => $this->resource->getAttribute('cover_image_path'),
            'rating' => $this->resource->getAttribute('rating'),
            'slogan' => $this->resource->getAttribute('slogan'),
            'category_reference' => $this->resource->getAttribute('category_reference'),
            'full_address' => $this->resource->getAttribute('full_address'),
            'latitude' => $this->resource->getAttribute('latitude'),
            'longitude' => $this->resource->getAttribute('longitude'),
            'delivery_charge' => $this->resource->getAttribute('delivery_charge'),
            'minimum_order_amount' => $this->resource->getAttribute('minimum_order_amount'),
            'zone' => $this->whenLoaded('zone', fn (): ?array => $this->resource->getRelation('zone') ? [
                'id' => $this->resource->getRelation('zone')->getKey(),
                'title' => $this->resource->getRelation('zone')->getAttribute('title'),
            ] : null),
        ];
    }
}
