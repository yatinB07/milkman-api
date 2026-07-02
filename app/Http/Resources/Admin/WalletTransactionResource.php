<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'customer_id' => $this->resource->getAttribute('customer_id'),
            'message' => $this->resource->getAttribute('message'),
            'type' => $this->resource->getAttribute('type'),
            'amount' => $this->resource->getAttribute('amount'),
            'transacted_at' => $this->resource->getAttribute('transacted_at')?->toISOString(),
            'customer' => $this->whenLoaded('customer', fn (): ?array => $this->resource->getRelation('customer') ? [
                'id' => $this->resource->getRelation('customer')->getKey(),
                'name' => $this->resource->getRelation('customer')->getAttribute('name'),
                'email' => $this->resource->getRelation('customer')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('customer')->getAttribute('mobile'),
                'wallet_balance' => $this->resource->getRelation('customer')->getAttribute('wallet_balance'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
