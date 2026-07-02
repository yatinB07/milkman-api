<?php

namespace App\Repositories;

use App\Exceptions\Catalog\CustomerAddressNotFoundException;
use App\Models\CustomerAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerAddressRepository
{
    /** @return LengthAwarePaginator<int, CustomerAddress> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return CustomerAddress::query()
            ->with('customer')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('address', 'like', "%{$search}%")
                        ->orWhere('landmark', 'like', "%{$search}%")
                        ->orWhere('rider_instruction', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('address')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): CustomerAddress
    {
        return CustomerAddress::query()->create($attributes)->load('customer');
    }

    public function find(int $id): CustomerAddress
    {
        $address = CustomerAddress::query()
            ->with('customer')
            ->find($id);

        if (! $address) {
            throw new CustomerAddressNotFoundException;
        }

        return $address;
    }

    /** @param array<string, mixed> $attributes */
    public function update(CustomerAddress $address, array $attributes): CustomerAddress
    {
        $address->update($attributes);

        return $address->refresh()->load('customer');
    }

    public function delete(CustomerAddress $address): void
    {
        $address->delete();
    }
}
