<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'description' => $this->resource->getAttribute('description'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
            ] : null),
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
                'is_out_of_stock' => $variant->getAttribute('is_out_of_stock'),
                'is_subscription_required' => $variant->getAttribute('is_subscription_required'),
            ])->values(),
            'images' => collect([$this->resource->getAttribute('image_path')])
                ->filter()
                ->merge($this->resource->getRelation('images')->pluck('image_path'))
                ->values()
                ->map(fn (string $imagePath): array => ['image_path' => $imagePath]),
        ];
    }
}
