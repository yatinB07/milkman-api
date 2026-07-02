<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class CustomerStoreDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...new CustomerStoreResource($this->resource)->toArray($request),
            'mobile' => $this->resource->getAttribute('mobile'),
            'email' => $this->resource->getAttribute('email'),
            'opens_at' => $this->resource->getAttribute('opens_at')?->format('H:i:s'),
            'closes_at' => $this->resource->getAttribute('closes_at')?->format('H:i:s'),
            'cancel_policy' => $this->resource->getAttribute('cancel_policy'),
            'is_pickup_enabled' => $this->resource->getAttribute('is_pickup_enabled'),
            'landmark' => $this->resource->getAttribute('landmark'),
            'distance_km' => $this->resource->getAttribute('distance_km'),
            'gallery_images' => $this->resource->getRelation('galleryImages')->map(fn ($image): array => [
                'id' => $image->getKey(),
                'image_path' => $image->getAttribute('image_path'),
            ])->values(),
            'faqs' => $this->resource->getRelation('faqs')->map(fn ($faq): array => [
                'id' => $faq->getKey(),
                'question' => $faq->getAttribute('question'),
                'answer' => $faq->getAttribute('answer'),
            ])->values(),
            'categories' => $this->categories(),
            'reviews' => $this->reviews(),
        ];
    }

    private function categories(): Collection
    {
        return $this->resource->getRelation('storeCategories')
            ->map(fn ($category): array => [
                'id' => $category->getKey(),
                'title' => $category->getAttribute('title'),
                'image_path' => $category->getAttribute('image_path'),
                'products' => $category->getRelation('products')->map(fn ($product): array => [
                    'id' => $product->getKey(),
                    'title' => $product->getAttribute('title'),
                    'image_path' => $product->getAttribute('image_path'),
                    'description' => $product->getAttribute('description'),
                    'variants' => $product->getRelation('variants')->map(fn ($variant): array => [
                        'id' => $variant->getKey(),
                        'title' => $variant->getAttribute('title'),
                        'normal_price' => $variant->getAttribute('normal_price'),
                        'subscribe_price' => $variant->getAttribute('subscribe_price'),
                        'discount' => $variant->getAttribute('discount'),
                        'is_out_of_stock' => $variant->getAttribute('is_out_of_stock'),
                        'is_subscription_required' => $variant->getAttribute('is_subscription_required'),
                    ])->values(),
                ])->values(),
            ])
            ->values();
    }

    private function reviews(): Collection
    {
        return $this->resource->getRelation('orders')
            ->toBase()
            ->concat($this->resource->getRelation('subscriptionOrders'))
            ->sortByDesc(fn ($order) => $order->getAttribute('reviewed_at'))
            ->map(fn ($order): array => [
                'id' => $order->getKey(),
                'type' => $order->getTable() === 'subscription_orders' ? 'subscription' : 'normal',
                'rating' => $order->getAttribute('total_rating'),
                'description' => $order->getAttribute('rating_text'),
                'reviewed_at' => $order->getAttribute('reviewed_at')?->toISOString(),
                'customer' => $order->getRelation('customer') ? [
                    'id' => $order->getRelation('customer')->getKey(),
                    'name' => $order->getRelation('customer')->getAttribute('name'),
                    'profile_image_path' => $order->getRelation('customer')->getAttribute('profile_image_path'),
                ] : null,
            ])
            ->values();
    }
}
