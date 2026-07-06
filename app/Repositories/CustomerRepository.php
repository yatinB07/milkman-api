<?php

namespace App\Repositories;

use App\Exceptions\Auth\CustomerPasswordResetIdentityNotFoundException;
use App\Exceptions\Catalog\CustomerNotFoundException;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    /** @return LengthAwarePaginator<int, Customer> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('referral_code', 'like', "%{$search}%")
                        ->orWhere('parent_referral_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Customer
    {
        return Customer::query()->create($attributes);
    }

    public function find(int $id): Customer
    {
        $customer = Customer::query()->find($id);

        if (! $customer) {
            throw new CustomerNotFoundException;
        }

        return $customer;
    }

    public function existsByEmail(string $email): bool
    {
        return Customer::withTrashed()
            ->where('email', $email)
            ->exists();
    }

    public function existsByCountryCodeAndMobile(string $countryCode, string $mobile): bool
    {
        return Customer::withTrashed()
            ->where('country_code', $countryCode)
            ->where('mobile', $mobile)
            ->exists();
    }

    public function findByCountryCodeAndMobile(string $countryCode, string $mobile): Customer
    {
        $customer = Customer::query()
            ->where('country_code', $countryCode)
            ->where('mobile', $mobile)
            ->first();

        if (! $customer) {
            throw new CustomerPasswordResetIdentityNotFoundException;
        }

        return $customer;
    }

    public function findLoginCandidateByCountryCodeAndIdentifier(string $countryCode, string $identifier): ?Customer
    {
        return Customer::query()
            ->where('country_code', $countryCode)
            ->where(function ($query) use ($identifier): void {
                $query->where('mobile', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();
    }

    public function updatePassword(Customer $customer, string $password): Customer
    {
        $customer->update(['password' => $password]);

        return $customer->refresh();
    }

    /** @param array<string, mixed> $attributes */
    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer->refresh();
    }

    public function debitWallet(Customer $customer, float $amount): Customer
    {
        $customer->forceFill([
            'wallet_balance' => (float) $customer->getAttribute('wallet_balance') - $amount,
        ])->save();

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
