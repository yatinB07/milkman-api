<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'message' => $this->resource->getAttribute('message'),
            'type' => $this->resource->getAttribute('type'),
            'amount' => $this->resource->getAttribute('amount'),
            'transacted_at' => $this->resource->getAttribute('transacted_at')?->toISOString(),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
