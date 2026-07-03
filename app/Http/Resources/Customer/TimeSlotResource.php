<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeSlotResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'starts_at' => $this->resource->getAttribute('starts_at')?->format('H:i:s'),
            'ends_at' => $this->resource->getAttribute('ends_at')?->format('H:i:s'),
            'is_active' => $this->resource->getAttribute('is_active'),
        ];
    }
}
