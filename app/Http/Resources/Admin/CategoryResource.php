<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'cover_path' => $this->resource->getAttribute('cover_path'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
