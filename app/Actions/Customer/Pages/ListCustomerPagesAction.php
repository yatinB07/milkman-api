<?php

namespace App\Actions\Customer\Pages;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerPagesAction
{
    public function __construct(private readonly PageRepository $pages) {}

    /** @return LengthAwarePaginator<int, Page> */
    public function execute(ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->pages->paginateActive($query->search, $query->perPage);
    }
}
