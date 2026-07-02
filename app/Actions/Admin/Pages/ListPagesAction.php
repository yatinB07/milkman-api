<?php

namespace App\Actions\Admin\Pages;

use App\Data\Admin\ListQueryData;
use App\Repositories\PageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPagesAction
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->pages->paginate($query->search, $query->perPage);
    }
}
