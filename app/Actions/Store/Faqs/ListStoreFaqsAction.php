<?php

namespace App\Actions\Store\Faqs;

use App\Data\Store\ListStoreQueryData;
use App\Models\Faq;
use App\Models\Store;
use App\Repositories\FaqRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreFaqsAction
{
    public function __construct(private readonly FaqRepository $faqs) {}

    /** @return LengthAwarePaginator<int, Faq> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->faqs->paginateForStore($store, $query->search, $query->perPage);
    }
}
