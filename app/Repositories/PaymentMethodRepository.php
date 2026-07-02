<?php

namespace App\Repositories;

use App\Exceptions\Catalog\PaymentMethodNotFoundException;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentMethodRepository
{
    /** @return LengthAwarePaginator<int, PaymentMethod> */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PaymentMethod::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PaymentMethod
    {
        return PaymentMethod::query()->create($attributes);
    }

    public function find(int $id): PaymentMethod
    {
        $method = PaymentMethod::query()->find($id);

        if (! $method) {
            throw new PaymentMethodNotFoundException;
        }

        return $method;
    }

    /** @return LengthAwarePaginator<int, PaymentMethod> */
    public function paginateVisibleActive(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PaymentMethod::query()
            ->where('is_visible', true)
            ->where('is_active', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('image_path', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);
    }

    /** @param array<string, mixed> $attributes */
    public function update(PaymentMethod $method, array $attributes): PaymentMethod
    {
        $method->update($attributes);

        return $method->refresh();
    }

    public function delete(PaymentMethod $method): void
    {
        $method->delete();
    }
}
