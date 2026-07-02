<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\StoreSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerHomeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'banners' => HomeBannerResource::collection($this->resource['banners'])->resolve($request),
            'categories' => CategoryResource::collection($this->resource['categories'])->resolve($request),
            'favorite_stores' => StoreSummaryResource::collection($this->resource['favorite_stores'])->resolve($request),
            'spotlight_stores' => StoreSummaryResource::collection($this->resource['spotlight_stores'])->resolve($request),
            'top_stores' => StoreSummaryResource::collection($this->resource['top_stores'])->resolve($request),
            'currency' => $this->resource['currency'],
            'wallet_balance' => $this->resource['wallet_balance'],
            'location' => $this->resource['location'],
        ];
    }
}
