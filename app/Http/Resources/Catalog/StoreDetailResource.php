<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...new StoreSummaryResource($this->resource)->toArray($request),
            'mobile' => $this->resource->getAttribute('mobile'),
            'opens_at' => $this->resource->getAttribute('opens_at')?->format('H:i:s'),
            'closes_at' => $this->resource->getAttribute('closes_at')?->format('H:i:s'),
            'is_pickup_enabled' => $this->resource->getAttribute('is_pickup_enabled'),
            'short_description' => $this->resource->getAttribute('short_description'),
            'content_description' => $this->resource->getAttribute('content_description'),
            'gallery_images' => $this->resource->getRelation('galleryImages')->map(fn ($image): array => [
                'id' => $image->getKey(),
                'image_path' => $image->getAttribute('image_path'),
            ])->values(),
            'delivery_options' => $this->resource->getRelation('deliveryOptions')->map(fn ($option): array => [
                'id' => $option->getKey(),
                'title' => $option->getAttribute('title'),
                'delivery_days' => $option->getAttribute('delivery_days'),
            ])->values(),
            'time_slots' => $this->resource->getRelation('timeSlots')->map(fn ($slot): array => [
                'id' => $slot->getKey(),
                'starts_at' => $slot->getAttribute('starts_at')?->format('H:i:s'),
                'ends_at' => $slot->getAttribute('ends_at')?->format('H:i:s'),
            ])->values(),
            'coupons' => $this->resource->getRelation('coupons')->map(fn ($coupon): array => [
                'id' => $coupon->getKey(),
                'title' => $coupon->getAttribute('title'),
                'code' => $coupon->getAttribute('code'),
                'minimum_amount' => $coupon->getAttribute('minimum_amount'),
                'value' => $coupon->getAttribute('value'),
                'expires_at' => $coupon->getAttribute('expires_at')?->toDateString(),
            ])->values(),
            'faqs' => $this->resource->getRelation('faqs')->map(fn ($faq): array => [
                'id' => $faq->getKey(),
                'question' => $faq->getAttribute('question'),
                'answer' => $faq->getAttribute('answer'),
            ])->values(),
        ];
    }
}
