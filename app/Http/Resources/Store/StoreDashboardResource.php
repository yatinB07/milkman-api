<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreDashboardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
            'financials' => $this->resource['financials'],
            'cards' => $this->resource['cards'],
            'withdraw_limit' => $this->resource['withdraw_limit'],
        ];
    }
}
