<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'title' => $this->resource->getAttribute('title'),
            'delivery_days' => $this->resource->getAttribute('delivery_days'),
            'is_active' => $this->resource->getAttribute('is_active'),
        ];
    }
}
