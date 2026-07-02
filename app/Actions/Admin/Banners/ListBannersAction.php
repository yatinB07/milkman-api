<?php

namespace App\Actions\Admin\Banners;

use App\Data\Admin\ListQueryData;
use App\Repositories\BannerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBannersAction
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->banners->paginate($query->search, $query->perPage);
    }
}
