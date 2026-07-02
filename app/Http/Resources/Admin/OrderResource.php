<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'store_id' => $this->resource->getAttribute('store_id'),
            'customer_id' => $this->resource->getAttribute('customer_id'),
            'ordered_at' => $this->resource->getAttribute('ordered_at')?->toISOString(),
            'payment_method_id' => $this->resource->getAttribute('payment_method_id'),
            'address' => $this->resource->getAttribute('address'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'delivery_charge' => $this->resource->getAttribute('delivery_charge'),
            'coupon_id' => $this->resource->getAttribute('coupon_id'),
            'coupon_amount' => $this->resource->getAttribute('coupon_amount'),
            'total' => $this->resource->getAttribute('total'),
            'subtotal' => $this->resource->getAttribute('subtotal'),
            'transaction_id' => $this->resource->getAttribute('transaction_id'),
            'admin_note' => $this->resource->getAttribute('admin_note'),
            'admin_status' => $this->resource->getAttribute('admin_status'),
            'rider_id' => $this->resource->getAttribute('rider_id'),
            'wallet_amount' => $this->resource->getAttribute('wallet_amount'),
            'customer_name' => $this->resource->getAttribute('customer_name'),
            'customer_mobile' => $this->resource->getAttribute('customer_mobile'),
            'status' => $this->resource->getAttribute('status'),
            'rejection_comment' => $this->resource->getAttribute('rejection_comment'),
            'time_slot' => $this->resource->getAttribute('time_slot'),
            'order_type' => $this->resource->getAttribute('order_type'),
            'is_rated' => $this->resource->getAttribute('is_rated'),
            'reviewed_at' => $this->resource->getAttribute('reviewed_at')?->toISOString(),
            'total_rating' => $this->resource->getAttribute('total_rating'),
            'rating_text' => $this->resource->getAttribute('rating_text'),
            'commission_percent' => $this->resource->getAttribute('commission_percent'),
            'store_charge' => $this->resource->getAttribute('store_charge'),
            'internal_status' => $this->resource->getAttribute('internal_status'),
            'signature_path' => $this->resource->getAttribute('signature_path'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
                'email' => $this->resource->getRelation('store')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('store')->getAttribute('mobile'),
            ] : null),
            'customer' => $this->whenLoaded('customer', fn (): ?array => $this->resource->getRelation('customer') ? [
                'id' => $this->resource->getRelation('customer')->getKey(),
                'name' => $this->resource->getRelation('customer')->getAttribute('name'),
                'email' => $this->resource->getRelation('customer')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('customer')->getAttribute('mobile'),
            ] : null),
            'payment_method' => $this->whenLoaded('paymentMethod', fn (): ?array => $this->resource->getRelation('paymentMethod') ? [
                'id' => $this->resource->getRelation('paymentMethod')->getKey(),
                'title' => $this->resource->getRelation('paymentMethod')->getAttribute('title'),
            ] : null),
            'rider' => $this->whenLoaded('rider', fn (): ?array => $this->resource->getRelation('rider') ? [
                'id' => $this->resource->getRelation('rider')->getKey(),
                'name' => $this->resource->getRelation('rider')->getAttribute('name'),
                'email' => $this->resource->getRelation('rider')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('rider')->getAttribute('mobile'),
            ] : null),
            'items' => $this->whenLoaded('items', fn (): array => $this->resource->getRelation('items')->map(fn ($item): array => [
                'id' => $item->getKey(),
                'quantity' => $item->getAttribute('quantity'),
                'product_title' => $item->getAttribute('product_title'),
                'discount' => $item->getAttribute('discount'),
                'image_path' => $item->getAttribute('image_path'),
                'price' => $item->getAttribute('price'),
                'variant_title' => $item->getAttribute('variant_title'),
            ])->all()),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
