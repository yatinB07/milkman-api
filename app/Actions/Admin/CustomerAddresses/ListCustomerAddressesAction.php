<?php

namespace App\Actions\Admin\CustomerAddresses;

use App\Data\Admin\ListQueryData;
use App\Repositories\CustomerAddressRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerAddressesAction
{
    public function __construct(
        private readonly CustomerAddressRepository $addresses,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->addresses->paginate($query->search, $query->perPage);
    }
}
