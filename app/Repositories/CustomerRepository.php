<?php

namespace App\Repositories;

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

    /** @param array<string, mixed> $attributes */
    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
