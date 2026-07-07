<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminEarningReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'store' => $this->resource['store'],
            'sale_count' => $this->resource['sale_count'],
            'total_amount' => $this->resource['total_amount'],
            'cash_on_hand_amount' => $this->resource['cash_on_hand_amount'],
            'delivery_charge' => $this->resource['delivery_charge'],
            'platform_earning' => $this->resource['platform_earning'],
            'store_payout' => $this->resource['store_payout'],
            'store_remaining_amount' => $this->resource['store_remaining_amount'],
            'rating' => $this->resource['rating'],
        ];
    }
}
