<?php

namespace App\Http\Resources\Rider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderNotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'rider_id' => $this->resource->getAttribute('rider_id'),
            'notified_at' => $this->resource->getAttribute('notified_at')?->toISOString(),
            'title' => $this->resource->getAttribute('title'),
            'message' => $this->resource->getAttribute('message'),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
