<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'coordinates' => $this->resource->getAttribute('coordinates'),
            'alias' => $this->resource->getAttribute('alias'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
