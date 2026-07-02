<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(private readonly WalletTransactionRepository $transactions) {}

    public function credit(Customer $customer, string $amount, string $message): WalletTransaction
    {
        return DB::transaction(function () use ($customer, $amount, $message): WalletTransaction {
            $customer->forceFill([
                'wallet_balance' => number_format(((float) $customer->getAttribute('wallet_balance')) + ((float) $amount), 2, '.', ''),
            ])->save();

            return $this->transactions->create([
                'customer_id' => $customer->getKey(),
                'message' => $message,
                'type' => 'Credit',
                'amount' => $amount,
                'transacted_at' => now(),
            ]);
        });
    }
}
