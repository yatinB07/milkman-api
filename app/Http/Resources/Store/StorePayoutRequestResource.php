<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePayoutRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'amount' => $this->resource->getAttribute('amount'),
            'status' => $this->resource->getAttribute('status'),
            'proof_path' => $this->resource->getAttribute('proof_path'),
            'requested_at' => $this->resource->getAttribute('requested_at')?->toISOString(),
            'request_type' => $this->resource->getAttribute('request_type'),
            'account_number' => $this->resource->getAttribute('account_number'),
            'bank_name' => $this->resource->getAttribute('bank_name'),
            'account_name' => $this->resource->getAttribute('account_name'),
            'ifsc_code' => $this->resource->getAttribute('ifsc_code'),
            'upi_id' => $this->resource->getAttribute('upi_id'),
            'paypal_id' => $this->resource->getAttribute('paypal_id'),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
