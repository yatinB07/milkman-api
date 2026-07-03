<?php

namespace App\Repositories;

use App\Exceptions\Catalog\PayoutRequestNotFoundException;
use App\Models\PayoutRequest;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayoutRequestRepository
{
    /** @return LengthAwarePaginator<int, PayoutRequest> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PayoutRequest::query()
            ->with('store')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('status', 'like', "%{$search}%")
                        ->orWhere('request_type', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_name', 'like', "%{$search}%")
                        ->orWhere('ifsc_code', 'like', "%{$search}%")
                        ->orWhere('upi_id', 'like', "%{$search}%")
                        ->orWhere('paypal_id', 'like', "%{$search}%")
                        ->orWhereHas('store', function ($query) use ($search): void {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('requested_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PayoutRequest
    {
        return PayoutRequest::query()->create($attributes)->load('store');
    }

    public function find(int $id): PayoutRequest
    {
        $payout = PayoutRequest::query()
            ->with('store')
            ->find($id);

        if (! $payout) {
            throw new PayoutRequestNotFoundException;
        }

        return $payout;
    }

    /** @return LengthAwarePaginator<int, PayoutRequest> */
    public function paginateForStore(Store $store, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PayoutRequest::query()
            ->whereBelongsTo($store)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('status', 'like', "%{$search}%")
                        ->orWhere('request_type', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_name', 'like', "%{$search}%")
                        ->orWhere('ifsc_code', 'like', "%{$search}%")
                        ->orWhere('upi_id', 'like', "%{$search}%")
                        ->orWhere('paypal_id', 'like', "%{$search}%");
                });
            })
            ->latest('requested_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function createForStore(Store $store, array $attributes): PayoutRequest
    {
        return PayoutRequest::query()
            ->create(array_merge($attributes, ['store_id' => $store->getKey()]));
    }

    public function findForStore(Store $store, int $id): PayoutRequest
    {
        $payout = PayoutRequest::query()
            ->whereBelongsTo($store)
            ->find($id);

        if (! $payout) {
            throw new PayoutRequestNotFoundException;
        }

        return $payout;
    }

    /** @param array<string, mixed> $attributes */
    public function update(PayoutRequest $payout, array $attributes): PayoutRequest
    {
        $payout->update($attributes);

        return $payout->refresh()->load('store');
    }

    public function delete(PayoutRequest $payout): void
    {
        $payout->delete();
    }
}
