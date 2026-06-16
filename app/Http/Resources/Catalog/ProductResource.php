<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'description' => $this->resource->getAttribute('description'),
            'store_category' => $this->whenLoaded('storeCategory', fn (): ?array => $this->resource->getRelation('storeCategory') ? [
                'id' => $this->resource->getRelation('storeCategory')->getKey(),
                'title' => $this->resource->getRelation('storeCategory')->getAttribute('title'),
            ] : null),
            'variants' => $this->resource->getRelation('variants')->map(fn ($variant): array => [
                'id' => $variant->getKey(),
                'title' => $variant->getAttribute('title'),
                'normal_price' => $variant->getAttribute('normal_price'),
                'subscribe_price' => $variant->getAttribute('subscribe_price'),
                'discount' => $variant->getAttribute('discount'),
                'is_subscription_required' => $variant->getAttribute('is_subscription_required'),
            ])->values(),
            'images' => $this->resource->getRelation('images')->map(fn ($image): array => [
                'id' => $image->getKey(),
                'image_path' => $image->getAttribute('image_path'),
            ])->values(),
        ];
    }
}
