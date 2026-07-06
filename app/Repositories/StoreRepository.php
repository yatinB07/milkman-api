<?php

namespace App\Repositories;

use App\Data\Customer\CustomerStoreSearchQueryData;
use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\CashCollection;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryOption;
use App\Models\Faq;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreGalleryImage;
use App\Models\SubscriptionOrder;
use App\Models\TimeSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
    /** @return LengthAwarePaginator<int, Store> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Store::query()
            ->with('zone')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhereHas('zone', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @return Collection<int, Store> */
    public function activeForHome(int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Store> */
    public function topRatedForHome(int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Store> */
    public function favoriteStoresForHome(Customer $customer, int $limit): Collection
    {
        return Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->whereHas('favorites', function ($query) use ($customer): void {
                $query->where('customer_id', $customer->getKey());
            })
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /** @return LengthAwarePaginator<int, Store> */
    public function paginateForCustomer(Customer $customer, CustomerStoreSearchQueryData $query): LengthAwarePaginator
    {
        $categoryTitle = $query->categoryId
            ? Category::query()->whereKey($query->categoryId)->value('title')
            : null;

        return Store::query()
            ->with([
                'zone',
                'coupons' => fn ($couponQuery) => $couponQuery
                    ->where('is_active', true)
                    ->orderBy('title')
                    ->limit(1),
            ])
            ->withCount('favorites')
            ->withExists([
                'favorites as is_favorite' => fn ($favoriteQuery) => $favoriteQuery
                    ->where('customer_id', $customer->getKey()),
            ])
            ->where('is_active', true)
            ->when($query->search, function ($builder, string $search): void {
                $builder->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('full_address', 'like', "%{$search}%")
                        ->orWhere('slogan', 'like', "%{$search}%")
                        ->orWhere('slogan_title', 'like', "%{$search}%")
                        ->orWhere('category_reference', 'like', "%{$search}%");
                });
            })
            ->when($categoryTitle, function ($builder, string $categoryTitle): void {
                $builder->where('category_reference', 'like', "%{$categoryTitle}%");
            })
            ->latest('id')
            ->paginate($query->perPage);
    }

    public function findActiveForCustomer(Customer $customer, int $storeId): Store
    {
        $store = Store::query()
            ->with([
                'zone',
                'galleryImages' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
                'faqs' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
                'storeCategories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with([
                        'products' => fn ($productQuery) => $productQuery
                            ->where('is_active', true)
                            ->whereHas('variants')
                            ->with([
                                'variants' => fn ($variantQuery) => $variantQuery->orderBy('id'),
                            ])
                            ->orderBy('title'),
                    ])
                    ->orderBy('title'),
                'orders' => fn ($query) => $query
                    ->with('customer')
                    ->where('status', 'Completed')
                    ->where('is_rated', true)
                    ->latest('reviewed_at'),
                'subscriptionOrders' => fn ($query) => $query
                    ->with('customer')
                    ->where('status', 'Completed')
                    ->where('is_rated', true)
                    ->latest('reviewed_at'),
            ])
            ->withCount('favorites')
            ->withExists([
                'favorites as is_favorite' => fn ($favoriteQuery) => $favoriteQuery
                    ->where('customer_id', $customer->getKey()),
            ])
            ->where('is_active', true)
            ->find($storeId);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }

    public function findActiveForCart(int $storeId): Store
    {
        $store = Store::query()
            ->with('zone')
            ->where('is_active', true)
            ->find($storeId);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }

    /** @return array<string, mixed> */
    public function dashboardMetrics(Store $store): array
    {
        $storeId = (int) $store->getKey();
        $counts = [
            'products' => Product::query()->where('store_id', $storeId)->count(),
            'product_variants' => ProductVariant::query()->where('store_id', $storeId)->count(),
            'delivery_options' => DeliveryOption::query()->where('store_id', $storeId)->count(),
            'store_categories' => StoreCategory::query()->where('store_id', $storeId)->count(),
            'faqs' => Faq::query()->where('store_id', $storeId)->count(),
            'time_slots' => TimeSlot::query()->where('store_id', $storeId)->count(),
            'coupons' => Coupon::query()->where('store_id', $storeId)->count(),
            'riders' => Rider::query()->where('store_id', $storeId)->count(),
            'product_images' => ProductImage::query()->where('store_id', $storeId)->count(),
            'gallery_images' => StoreGalleryImage::query()->where('store_id', $storeId)->count(),
            'normal_orders' => Order::query()->where('store_id', $storeId)->count(),
            'subscription_orders' => SubscriptionOrder::query()->where('store_id', $storeId)->count(),
        ];
        $payout = (float) PayoutRequest::query()->where('store_id', $storeId)->sum('amount');
        $normalEarning = $this->normalOrderEarning($storeId);
        $subscriptionEarning = $this->subscriptionOrderEarning($storeId);
        $cashOrderTotal = $this->completedNormalOrderGross($storeId);
        $cashCollected = (float) CashCollection::query()->where('store_id', $storeId)->sum('amount');
        $financials = [
            'normal_order_earning' => number_format($normalEarning, 2, '.', ''),
            'subscription_order_earning' => number_format($subscriptionEarning, 2, '.', ''),
            'earning' => number_format($normalEarning + $subscriptionEarning - $payout, 2, '.', ''),
            'payout' => number_format($payout, 2, '.', ''),
            'on_hand_amount' => number_format(max(0, $cashOrderTotal - $cashCollected), 2, '.', ''),
        ];

        return [
            'counts' => $counts,
            'financials' => $financials,
            'cards' => $this->dashboardCards($counts, $financials),
            'withdraw_limit' => '0.00',
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Store
    {
        return Store::query()->create($attributes)->load('zone');
    }

    public function find(int $id): Store
    {
        $store = Store::query()
            ->with('zone')
            ->find($id);

        if (! $store) {
            throw new StoreNotFoundException;
        }

        return $store;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Store $store, array $attributes): Store
    {
        $store->update($attributes);

        return $store->refresh()->load('zone');
    }

    public function deactivateAccount(Store $store): Store
    {
        $store->update(['is_active' => false]);
        $store->tokens()->delete();

        return $store->refresh()->load('zone');
    }

    public function delete(Store $store): void
    {
        $store->delete();
    }

    private function normalOrderEarning(int $storeId): float
    {
        return (float) Order::query()
            ->where('store_id', $storeId)
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (Order $order): float => (((float) $order->getAttribute('subtotal')) - ((float) $order->getAttribute('coupon_amount')))
                - ((((float) $order->getAttribute('subtotal')) - ((float) $order->getAttribute('coupon_amount')) + ((float) $order->getAttribute('delivery_charge'))) * ((float) $order->getAttribute('commission_percent') / 100)));
    }

    private function subscriptionOrderEarning(int $storeId): float
    {
        return (float) SubscriptionOrder::query()
            ->where('store_id', $storeId)
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (SubscriptionOrder $order): float => (((float) $order->getAttribute('subtotal')) - ((float) $order->getAttribute('coupon_amount')) + ((float) $order->getAttribute('delivery_charge')))
                - ((((float) $order->getAttribute('subtotal')) - ((float) $order->getAttribute('coupon_amount')) + ((float) $order->getAttribute('delivery_charge'))) * ((float) $order->getAttribute('commission_percent') / 100)));
    }

    private function completedNormalOrderGross(int $storeId): float
    {
        return (float) Order::query()
            ->where('store_id', $storeId)
            ->where('status', 'Completed')
            ->get()
            ->sum(fn (Order $order): float => ((float) $order->getAttribute('subtotal')) - ((float) $order->getAttribute('coupon_amount')) + ((float) $order->getAttribute('delivery_charge')));
    }

    /**
     * @param  array<string, int>  $counts
     * @param  array<string, string>  $financials
     * @return array<int, array<string, mixed>>
     */
    private function dashboardCards(array $counts, array $financials): array
    {
        return [
            ['title' => 'Product', 'report_data' => $counts['products']],
            ['title' => 'Category', 'report_data' => $counts['store_categories']],
            ['title' => 'FAQ', 'report_data' => $counts['faqs']],
            ['title' => 'Timeslot', 'report_data' => $counts['time_slots']],
            ['title' => 'Coupon', 'report_data' => $counts['coupons']],
            ['title' => 'Rider', 'report_data' => $counts['riders']],
            ['title' => 'Extra Images', 'report_data' => $counts['product_images']],
            ['title' => 'Gallery Images', 'report_data' => $counts['gallery_images']],
            ['title' => 'Normal Order', 'report_data' => $counts['normal_orders']],
            ['title' => 'Earning', 'report_data' => $financials['earning']],
            ['title' => 'Payout', 'report_data' => $financials['payout']],
            ['title' => 'Subscription Order', 'report_data' => $counts['subscription_orders']],
            ['title' => 'Deliveries', 'report_data' => $counts['delivery_options']],
            ['title' => 'Product Attribute', 'report_data' => $counts['product_variants']],
            ['title' => 'Total On Hand Amount', 'report_data' => $financials['on_hand_amount']],
        ];
    }
}
