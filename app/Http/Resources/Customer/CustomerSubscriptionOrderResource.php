<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSubscriptionOrderResource extends JsonResource
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
            'wallet_amount' => $this->resource->getAttribute('wallet_amount'),
            'customer_name' => $this->resource->getAttribute('customer_name'),
            'customer_mobile' => $this->resource->getAttribute('customer_mobile'),
            'status' => $this->resource->getAttribute('status'),
            'order_type' => $this->resource->getAttribute('order_type'),
            'is_rated' => $this->resource->getAttribute('is_rated'),
            'reviewed_at' => $this->resource->getAttribute('reviewed_at')?->toISOString(),
            'total_rating' => $this->resource->getAttribute('total_rating'),
            'rating_text' => $this->resource->getAttribute('rating_text'),
            'store' => $this->whenLoaded('store', fn (): ?array => $this->resource->getRelation('store') ? [
                'id' => $this->resource->getRelation('store')->getKey(),
                'title' => $this->resource->getRelation('store')->getAttribute('title'),
            ] : null),
            'payment_method' => $this->whenLoaded('paymentMethod', fn (): ?array => $this->resource->getRelation('paymentMethod') ? [
                'id' => $this->resource->getRelation('paymentMethod')->getKey(),
                'title' => $this->resource->getRelation('paymentMethod')->getAttribute('title'),
            ] : null),
            'rider' => $this->whenLoaded('rider', fn (): ?array => $this->resource->getRelation('rider') ? [
                'id' => $this->resource->getRelation('rider')->getKey(),
                'name' => $this->resource->getRelation('rider')->getAttribute('name'),
                'image_path' => $this->resource->getRelation('rider')->getAttribute('image_path'),
                'country_code' => $this->resource->getRelation('rider')->getAttribute('country_code'),
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
                'starts_at' => $item->getAttribute('starts_at')?->toDateString(),
                'total_deliveries' => $item->getAttribute('total_deliveries'),
                'total_dates' => $item->getAttribute('total_dates'),
                'completed_dates' => $item->getAttribute('completed_dates'),
                'selected_days' => $item->getAttribute('selected_days'),
                'time_slot' => $item->getAttribute('time_slot'),
                'schedule' => $this->schedule($item->getAttribute('total_dates'), $item->getAttribute('completed_dates')),
            ])->values()->all()),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function schedule(?string $totalDates, ?string $completedDates): array
    {
        $completed = collect(explode(',', (string) $completedDates))
            ->filter()
            ->values()
            ->all();

        return collect(explode(',', (string) $totalDates))
            ->filter()
            ->map(fn (string $date): array => [
                'date' => $date,
                'is_complete' => in_array($date, $completed, true),
                'format_date' => date('D d', strtotime($date)),
            ])
            ->values()
            ->all();
    }
}
