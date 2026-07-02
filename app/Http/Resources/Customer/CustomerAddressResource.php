<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAddressResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'address' => $this->resource->getAttribute('address'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'rider_instruction' => $this->resource->getAttribute('rider_instruction'),
            'type' => $this->resource->getAttribute('type'),
            'latitude' => $this->resource->getAttribute('latitude'),
            'longitude' => $this->resource->getAttribute('longitude'),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
