<?php

namespace App\Repositories;

use App\Exceptions\Catalog\WalletTransactionNotFoundException;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WalletTransactionRepository
{
    /** @return LengthAwarePaginator<int, WalletTransaction> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return WalletTransaction::query()
            ->with('customer')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('message', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('transacted_at')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): WalletTransaction
    {
        return WalletTransaction::query()->create($attributes)->load('customer');
    }

    public function find(int $id): WalletTransaction
    {
        $transaction = WalletTransaction::query()
            ->with('customer')
            ->find($id);

        if (! $transaction) {
            throw new WalletTransactionNotFoundException;
        }

        return $transaction;
    }

    /** @param array<string, mixed> $attributes */
    public function update(WalletTransaction $transaction, array $attributes): WalletTransaction
    {
        $transaction->update($attributes);

        return $transaction->refresh()->load('customer');
    }

    public function delete(WalletTransaction $transaction): void
    {
        $transaction->delete();
    }
}
