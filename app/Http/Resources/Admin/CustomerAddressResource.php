<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAddressResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'customer_id' => $this->resource->getAttribute('customer_id'),
            'address' => $this->resource->getAttribute('address'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'rider_instruction' => $this->resource->getAttribute('rider_instruction'),
            'type' => $this->resource->getAttribute('type'),
            'latitude' => $this->resource->getAttribute('latitude'),
            'longitude' => $this->resource->getAttribute('longitude'),
            'customer' => $this->whenLoaded('customer', fn (): ?array => $this->resource->getRelation('customer') ? [
                'id' => $this->resource->getRelation('customer')->getKey(),
                'name' => $this->resource->getRelation('customer')->getAttribute('name'),
                'email' => $this->resource->getRelation('customer')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('customer')->getAttribute('mobile'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
