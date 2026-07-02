<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreNotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'notified_at' => $this->resource->getAttribute('notified_at')?->toISOString(),
            'title' => $this->resource->getAttribute('title'),
            'description' => $this->resource->getAttribute('description'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
                'email' => $this->resource->getRelation('store')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('store')->getAttribute('mobile'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
