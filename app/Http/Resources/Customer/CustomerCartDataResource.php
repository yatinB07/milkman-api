<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerCartDataResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $store = $this->resource['store'];

        return [
            'store' => [
                'id' => $store->getKey(),
                'title' => $store->getAttribute('title'),
                'image_path' => $store->getAttribute('image_path'),
                'tags' => $store->getAttribute('short_description')
                    ? array_values(array_filter(array_map('trim', explode(',', $store->getAttribute('short_description')))))
                    : [],
                'full_address' => $store->getAttribute('full_address'),
                'store_charge' => $store->getAttribute('store_charge'),
                'minimum_order_amount' => $store->getAttribute('minimum_order_amount'),
                'is_open' => $store->getAttribute('registration_status'),
                'is_pickup_enabled' => $store->getAttribute('checkout_is_pickup_enabled'),
                'delivery_charge' => $store->getAttribute('checkout_delivery_charge'),
                'zone' => $store->relationLoaded('zone') && $store->getRelation('zone') ? [
                    'id' => $store->getRelation('zone')->getKey(),
                    'title' => $store->getRelation('zone')->getAttribute('title'),
                ] : null,
            ],
            'coupons' => CouponResource::collection($this->resource['coupons'])->resolve($request),
            'payment_methods' => PaymentMethodResource::collection($this->resource['payment_methods'])->resolve($request),
            'time_slots' => TimeSlotResource::collection($this->resource['time_slots'])->resolve($request),
        ];
    }
}
