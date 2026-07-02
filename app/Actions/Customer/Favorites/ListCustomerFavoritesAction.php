<?php

namespace App\Actions\Customer\Favorites;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Customer;
use App\Repositories\FavoriteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerFavoritesAction
{
    public function __construct(private readonly FavoriteRepository $favorites) {}

    public function execute(Customer $customer, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->favorites->paginateForCustomer($customer, $query->search, $query->perPage);
    }
}
