<?php

namespace App\Actions\Admin\ProductVariants;

use App\Data\Admin\ListQueryData;
use App\Repositories\ProductVariantRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductVariantsAction
{
    public function __construct(
        private readonly ProductVariantRepository $variants,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->variants->paginate($query->search, $query->perPage);
    }
}
