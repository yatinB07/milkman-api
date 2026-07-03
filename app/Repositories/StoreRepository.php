<?php

namespace App\Repositories;

use App\Data\Customer\CustomerStoreSearchQueryData;
use App\Exceptions\Catalog\StoreNotFoundException;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Store;
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

    public function delete(Store $store): void
    {
        $store->delete();
    }
}
