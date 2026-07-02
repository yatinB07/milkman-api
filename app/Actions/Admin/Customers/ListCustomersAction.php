<?php

namespace App\Actions\Admin\Customers;

use App\Data\Admin\ListQueryData;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomersAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->customers->paginate($query->search, $query->perPage);
    }
}
