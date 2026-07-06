<?php

namespace App\Actions\Rider\Pages;

use App\Data\Rider\ListRiderQueryData;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRiderPagesAction
{
    public function __construct(private readonly PageRepository $pages) {}

    /** @return LengthAwarePaginator<int, Page> */
    public function execute(ListRiderQueryData $query): LengthAwarePaginator
    {
        return $this->pages->paginateActive($query->search, $query->perPage);
    }
}
