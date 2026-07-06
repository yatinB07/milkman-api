<?php

namespace App\Http\Resources\Rider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderDashboardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
            'cards' => $this->resource['cards'],
            'withdraw_limit' => $this->resource['withdraw_limit'],
        ];
    }
}
