<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerStoreResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $coupon = $this->resource->relationLoaded('coupons')
            ? $this->resource->getRelation('coupons')->first()
            : null;

        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'cover_image_path' => $this->resource->getAttribute('cover_image_path'),
            'rating' => $this->resource->getAttribute('rating'),
            'slogan' => $this->resource->getAttribute('slogan'),
            'slogan_title' => $this->resource->getAttribute('slogan_title'),
            'short_description' => $this->resource->getAttribute('short_description'),
            'category_reference' => $this->resource->getAttribute('category_reference'),
            'full_address' => $this->resource->getAttribute('full_address'),
            'latitude' => $this->resource->getAttribute('latitude'),
            'longitude' => $this->resource->getAttribute('longitude'),
            'delivery_charge' => $this->resource->getAttribute('delivery_charge'),
            'minimum_order_amount' => $this->resource->getAttribute('minimum_order_amount'),
            'is_favorite' => (bool) $this->resource->getAttribute('is_favorite'),
            'total_favorites' => (int) $this->resource->getAttribute('favorites_count'),
            'coupon' => $coupon ? [
                'id' => $coupon->getKey(),
                'title' => $coupon->getAttribute('title'),
                'subtitle' => $coupon->getAttribute('subtitle'),
            ] : null,
            'zone' => $this->whenLoaded('zone', fn (): ?array => $this->resource->getRelation('zone') ? [
                'id' => $this->resource->getRelation('zone')->getKey(),
                'title' => $this->resource->getRelation('zone')->getAttribute('title'),
            ] : null),
        ];
    }
}
