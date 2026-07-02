<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CouponNotFoundException;
use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository
{
    /** @return LengthAwarePaginator<int, Coupon> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Coupon::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Coupon
    {
        return Coupon::query()->create($attributes)->load('store');
    }

    public function find(int $id): Coupon
    {
        $coupon = Coupon::query()
            ->with('store')
            ->find($id);

        if (! $coupon) {
            throw new CouponNotFoundException;
        }

        return $coupon;
    }

    /** @return LengthAwarePaginator<int, Coupon> */
    public function paginateActiveForStore(int $storeId, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Coupon::query()
            ->with('store')
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findActive(int $id): Coupon
    {
        $coupon = Coupon::query()
            ->with('store')
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->find($id);

        if (! $coupon) {
            throw new CouponNotFoundException;
        }

        return $coupon;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Coupon $coupon, array $attributes): Coupon
    {
        $coupon->update($attributes);

        return $coupon->refresh()->load('store');
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }
}
