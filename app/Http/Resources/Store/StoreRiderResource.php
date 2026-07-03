<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreRiderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'name' => $this->resource->getAttribute('name'),
            'email' => $this->resource->getAttribute('email'),
            'country_code' => $this->resource->getAttribute('country_code'),
            'mobile' => $this->resource->getAttribute('mobile'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'joined_at' => $this->resource->getAttribute('joined_at')?->toDateString(),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
