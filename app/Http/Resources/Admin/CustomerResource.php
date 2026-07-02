<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'name' => $this->resource->getAttribute('name'),
            'profile_image_path' => $this->resource->getAttribute('profile_image_path'),
            'email' => $this->resource->getAttribute('email'),
            'country_code' => $this->resource->getAttribute('country_code'),
            'mobile' => $this->resource->getAttribute('mobile'),
            'registered_at' => $this->resource->getAttribute('registered_at')?->toISOString(),
            'referral_code' => $this->resource->getAttribute('referral_code'),
            'parent_referral_code' => $this->resource->getAttribute('parent_referral_code'),
            'wallet_balance' => $this->resource->getAttribute('wallet_balance'),
            'is_active' => $this->resource->getAttribute('is_active'),
            'email_verified_at' => $this->resource->getAttribute('email_verified_at')?->toISOString(),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
