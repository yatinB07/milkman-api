<?php

namespace App\Actions\Admin\Products;

use App\Data\Admin\ListQueryData;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->products->paginate($query->search, $query->perPage);
    }
}
