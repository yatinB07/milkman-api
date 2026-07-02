<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'zone_id' => $this->resource->getAttribute('zone_id'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'cover_image_path' => $this->resource->getAttribute('cover_image_path'),
            'rating' => $this->resource->getAttribute('rating'),
            'slogan' => $this->resource->getAttribute('slogan'),
            'slogan_title' => $this->resource->getAttribute('slogan_title'),
            'language_code' => $this->resource->getAttribute('language_code'),
            'category_reference' => $this->resource->getAttribute('category_reference'),
            'email' => $this->resource->getAttribute('email'),
            'country_code' => $this->resource->getAttribute('country_code'),
            'mobile' => $this->resource->getAttribute('mobile'),
            'full_address' => $this->resource->getAttribute('full_address'),
            'pincode' => $this->resource->getAttribute('pincode'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'short_description' => $this->resource->getAttribute('short_description'),
            'content_description' => $this->resource->getAttribute('content_description'),
            'latitude' => $this->resource->getAttribute('latitude'),
            'longitude' => $this->resource->getAttribute('longitude'),
            'store_charge' => $this->resource->getAttribute('store_charge'),
            'delivery_charge' => $this->resource->getAttribute('delivery_charge'),
            'minimum_order_amount' => $this->resource->getAttribute('minimum_order_amount'),
            'commission_percent' => $this->resource->getAttribute('commission_percent'),
            'opens_at' => $this->resource->getAttribute('opens_at')?->format('H:i:s'),
            'closes_at' => $this->resource->getAttribute('closes_at')?->format('H:i:s'),
            'is_pickup_enabled' => $this->resource->getAttribute('is_pickup_enabled'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'registration_status' => $this->resource->getAttribute('registration_status'),
            'charge_type' => $this->resource->getAttribute('charge_type'),
            'unit_kilometers' => $this->resource->getAttribute('unit_kilometers'),
            'unit_price' => $this->resource->getAttribute('unit_price'),
            'additional_price' => $this->resource->getAttribute('additional_price'),
            'bank_name' => $this->resource->getAttribute('bank_name'),
            'ifsc_code' => $this->resource->getAttribute('ifsc_code'),
            'receipt_name' => $this->resource->getAttribute('receipt_name'),
            'account_number' => $this->resource->getAttribute('account_number'),
            'paypal_id' => $this->resource->getAttribute('paypal_id'),
            'upi_id' => $this->resource->getAttribute('upi_id'),
            'cancel_policy' => $this->resource->getAttribute('cancel_policy'),
            'zone' => $this->whenLoaded('zone', fn (): ?array => $this->resource->getRelation('zone') ? [
                'id' => $this->resource->getRelation('zone')->getKey(),
                'title' => $this->resource->getRelation('zone')->getAttribute('title'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
