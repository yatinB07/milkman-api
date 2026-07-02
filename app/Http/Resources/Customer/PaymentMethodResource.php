<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->getAttribute('title'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'attributes' => $this->resource->getAttribute('attributes'),
            'subtitle' => $this->resource->getAttribute('subtitle'),
            'is_visible' => $this->resource->getAttribute('is_visible'),
            'is_active' => $this->resource->getAttribute('is_active'),
        ];
    }
}
