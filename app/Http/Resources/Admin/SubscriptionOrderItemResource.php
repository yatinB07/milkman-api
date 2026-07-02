<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionOrderItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'subscription_order_id' => $this->resource->getAttribute('subscription_order_id'),
            'quantity' => $this->resource->getAttribute('quantity'),
            'product_title' => $this->resource->getAttribute('product_title'),
            'discount' => $this->resource->getAttribute('discount'),
            'image_path' => $this->resource->getAttribute('image_path'),
            'price' => $this->resource->getAttribute('price'),
            'variant_title' => $this->resource->getAttribute('variant_title'),
            'starts_at' => $this->resource->getAttribute('starts_at')?->toDateString(),
            'total_deliveries' => $this->resource->getAttribute('total_deliveries'),
            'total_dates' => $this->resource->getAttribute('total_dates'),
            'completed_dates' => $this->resource->getAttribute('completed_dates'),
            'selected_days' => $this->resource->getAttribute('selected_days'),
            'time_slot' => $this->resource->getAttribute('time_slot'),
            'subscription_order' => $this->whenLoaded('subscriptionOrder', fn (): ?array => $this->resource->getRelation('subscriptionOrder') ? [
                'id' => $this->resource->getRelation('subscriptionOrder')->getKey(),
                'transaction_id' => $this->resource->getRelation('subscriptionOrder')->getAttribute('transaction_id'),
                'customer_name' => $this->resource->getRelation('subscriptionOrder')->getAttribute('customer_name'),
                'customer_mobile' => $this->resource->getRelation('subscriptionOrder')->getAttribute('customer_mobile'),
                'status' => $this->resource->getRelation('subscriptionOrder')->getAttribute('status'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
