<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDashboardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
            'financials' => $this->resource['financials'],
            'cards' => $this->resource['cards'],
        ];
    }
}
