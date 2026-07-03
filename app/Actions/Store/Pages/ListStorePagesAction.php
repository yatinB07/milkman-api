<?php

namespace App\Actions\Store\Pages;

use App\Data\Store\ListStoreQueryData;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStorePagesAction
{
    public function __construct(private readonly PageRepository $pages) {}

    /** @return LengthAwarePaginator<int, Page> */
    public function execute(ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->pages->paginateActive($query->search, $query->perPage);
    }
}
